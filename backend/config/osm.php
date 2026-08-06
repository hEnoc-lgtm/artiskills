<?php
// ============================================================
// ArtiSkills — OpenStreetMap (Nominatim) + calcul géographique
// Emplacement : backend/config/osm.php
// ============================================================

// 1. Géocodage d'une adresse via Nominatim (aucune clé API)
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

// 2. Distance Haversine en km
function distanceKm($lat1, $lon1, $lat2, $lon2) {
    $R = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
    return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

// 3. Géocode un quartier UNIQUEMENT si ses coordonnées sont vides
function assurerCoordonneesQuartier($pdo, $id_quartier) {
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
    if (!$q) return false;

    if ($q['latitude'] !== null && $q['longitude'] !== null) return true; // déjà géolocalisé

    $geo = geocoderOSM($q['nom_quartier'] . ', ' . $q['nomCommune'] . ', ' . $q['nomDepartement'] . ', Bénin');
    if (!$geo) $geo = geocoderOSM($q['nomCommune'] . ', ' . $q['nomDepartement'] . ', Bénin');
    if (!$geo) return false;

    $pdo->prepare("UPDATE quartier_village SET latitude = ?, longitude = ? WHERE id_quartier = ?")
        ->execute([$geo['lat'], $geo['lon'], $id_quartier]);
    return true;
}

// 4. Affectation automatique au centre le plus proche (même département prioritaire)
function affecterAuCentreLePlusProche($pdo, $idTest) {
    $stmt = $pdo->prepare("
        SELECT t.score, t.code_corpsmetier, t.statutAffectation, a.id_quartier_residence,
               com.idDepart AS departementResidence
        FROM test t
        JOIN artisan a ON t.id_artisan = a.id_artisan
        LEFT JOIN quartier_village qv ON a.id_quartier_residence = qv.id_quartier
        LEFT JOIN arrondissement arr ON qv.id_arrondissement = arr.id_arrondissement
        LEFT JOIN commune com ON arr.idCommune = com.idCommune
        WHERE t.idTest = ?
    ");
    $stmt->execute([$idTest]);
    $test = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$test) return ["success" => false, "message" => "Test introuvable."];

    if (in_array($test['statutAffectation'], ['valide', 'rejete'])) {
        return ["success" => true, "message" => "Déjà traité."];
    }

    if ((int)$test['score'] < 5) {
        $pdo->prepare("UPDATE test SET statutAffectation = 'rejete' WHERE idTest = ?")->execute([$idTest]);
        return ["success" => true, "message" => "Score insuffisant.", "statut" => "rejete"];
    }

    if (!$test['id_quartier_residence']) {
        return ["success" => false, "message" => "Quartier de résidence manquant."];
    }

    assurerCoordonneesQuartier($pdo, $test['id_quartier_residence']);
    $g = $pdo->prepare("SELECT latitude, longitude FROM quartier_village WHERE id_quartier = ?");
    $g->execute([$test['id_quartier_residence']]);
    $geoArtisan = $g->fetch(PDO::FETCH_ASSOC);
    if (!$geoArtisan || $geoArtisan['latitude'] === null) {
        return ["success" => false, "message" => "Résidence non géolocalisée."];
    }

    $centres = $pdo->query("
        SELECT c.idCentre, c.nomCentre, com.idDepart AS departementCentre, qv.latitude, qv.longitude
        FROM centre_formation c
        JOIN quartier_village qv ON c.id_quartier_centre = qv.id_quartier
        JOIN arrondissement arr ON qv.id_arrondissement = arr.id_arrondissement
        JOIN commune com ON arr.idCommune = com.idCommune
        WHERE qv.latitude IS NOT NULL AND qv.longitude IS NOT NULL
    ")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($centres)) return ["success" => false, "message" => "Aucun centre géolocalisé."];

    // Priorité même département, sinon tous (le plus proche = département voisin)
    $candidats = array_values(array_filter($centres, fn($c) => $c['departementCentre'] == $test['departementResidence']));
    $memeDepartement = !empty($candidats);
    if (!$memeDepartement) $candidats = $centres;

    $meilleur = null; $distMin = INF;
    foreach ($candidats as $c) {
        $d = distanceKm($geoArtisan['latitude'], $geoArtisan['longitude'], $c['latitude'], $c['longitude']);
        if ($d < $distMin) { $distMin = $d; $meilleur = $c; }
    }
    if (!$meilleur) return ["success" => false, "message" => "Aucun centre exploitable."];

    // Places disponibles (objectif de formation du métier)
    $obj = $pdo->prepare("SELECT nombrePlaces FROM objectif_formation WHERE code_corpsmetier = ? ORDER BY idObjectif DESC LIMIT 1");
    $obj->execute([$test['code_corpsmetier']]);
    $objectif = $obj->fetch(PDO::FETCH_ASSOC);

    $placesPrises = 0;
    if ($objectif) {
        $cnt = $pdo->prepare("
            SELECT COUNT(*) FROM affectation af
            JOIN test t ON af.idTest = t.idTest
            WHERE t.code_corpsmetier = ? AND af.statutPlace = 'validée directement'
        ");
        $cnt->execute([$test['code_corpsmetier']]);
        $placesPrises = (int)$cnt->fetchColumn();
    }

    $placeDispo   = !$objectif || $placesPrises < (int)$objectif['nombrePlaces'];
    $statutPlace  = $placeDispo ? 'validée directement' : 'liste_attente';
    $statutAffect = $placeDispo ? 'valide' : 'en_attente';

    $pdo->beginTransaction();
    $ex = $pdo->prepare("SELECT idAffect FROM affectation WHERE idTest = ?");
    $ex->execute([$idTest]);
    $idAffect = $ex->fetchColumn();

    $ref = ($memeDepartement ? "Même département" : "Département voisin") . " — " . round($distMin, 2) . " km";

    if ($idAffect) {
        $pdo->prepare("UPDATE affectation SET distanceCalculee = ?, adresseReference = ?, statutPlace = ?, idCentre = ? WHERE idAffect = ?")
            ->execute([round($distMin, 2), $ref, $statutPlace, $meilleur['idCentre'], $idAffect]);
    } else {
        $pdo->prepare("INSERT INTO affectation (distanceCalculee, adresseReference, statutPlace, idTest, idCentre) VALUES (?, ?, ?, ?, ?)")
            ->execute([round($distMin, 2), $ref, $statutPlace, $idTest, $meilleur['idCentre']]);
    }
    $pdo->prepare("UPDATE test SET statutAffectation = ? WHERE idTest = ?")->execute([$statutAffect, $idTest]);
    $pdo->commit();

    return [
        "success" => true,
        "centre" => $meilleur['nomCentre'],
        "distance_km" => round($distMin, 2),
        "meme_departement" => $memeDepartement,
        "statutPlace" => $statutPlace
    ];
}
?>