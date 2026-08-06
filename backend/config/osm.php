<?php
// ============================================================
// ArtiSkills — OpenStreetMap (Nominatim) + Calcul de distance
// ============================================================

// 1. Géocodage via Nominatim (OpenStreetMap)
function geocoderOSM($adresse) {
    $url = "https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=bj&q=" . urlencode($adresse);
    $ctx = stream_context_create([
        "http" => [
            "header" => "User-Agent: ArtiSkills-ANPS/1.0 (stage@anps.bj)\r\n",
            "timeout" => 5
        ]
    ]);
    $json = @file_get_contents($url, false, $ctx);
    if (!$json) return null;
    $data = json_decode($json, true);
    return !empty($data) ? ["lat" => (float)$data[0]["lat"], "lon" => (float)$data[0]["lon"]] : null;
}

// 2. Distance à vol d'oiseau (Haversine) en km
function distanceKm($lat1, $lon1, $lat2, $lon2) {
    $R = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
    return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

// 3. Récupère les GPS d'un quartier (depuis la BDD ou via OSM si vide)
function obtenirCoordonneesQuartier($pdo, $id_quartier) {
    $stmt = $pdo->prepare("
        SELECT qv.latitude, qv.longitude, qv.nom_quartier, com.nomCommune, d.nomDepartement
        FROM quartier_village qv
        JOIN arrondissement arr ON qv.id_arrondissement = arr.id_arrondissement
        JOIN commune com ON arr.idCommune = com.idCommune
        JOIN departement d ON com.idDepart = d.idDepart
        WHERE qv.id_quartier = ?
    ");
    $stmt->execute([$id_quartier]);
    $q = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$q) return null;

    // Si les coordonnées existent déjà en base, on les retourne direct
    if ($q['latitude'] !== null && $q['longitude'] !== null) {
        return ["lat" => (float)$q['latitude'], "lon" => (float)$q['longitude']];
    }

    // Sinon on appelle OpenStreetMap
    $geo = geocoderOSM($q['nom_quartier'] . ', ' . $q['nomCommune'] . ', ' . $q['nomDepartement'] . ', Bénin');
    if (!$geo) {
        $geo = geocoderOSM($q['nomCommune'] . ', ' . $q['nomDepartement'] . ', Bénin');
    }

    // On sauvegarde pour les prochains
    if ($geo) {
        $pdo->prepare("UPDATE quartier_village SET latitude = ?, longitude = ? WHERE id_quartier = ?")
            ->execute([$geo['lat'], $geo['lon'], $id_quartier]);
    }

    return $geo;
}
?>