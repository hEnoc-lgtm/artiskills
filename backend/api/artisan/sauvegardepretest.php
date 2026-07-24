<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$idArtisan = $donnees['idArtisan'] ?? null;
$idCorpsMetier = $donnees['idCorpsMetier'] ?? null;
$anneesExperience = $donnees['anneesExperience'] ?? null;

// Données géo Résidence
$resArrondissementText = $donnees['residenceArrondissementText'] ?? '';
$resCommuneText = $donnees['residenceCommuneText'] ?? '';
$resDepartementText = $donnees['residenceDepartementText'] ?? '';
$resQuartier = $donnees['residenceQuartier'] ?? '';

// Données géo Atelier
$atlArrondissementText = $donnees['atelierArrondissementText'] ?? '';
$atlCommuneText = $donnees['atelierCommuneText'] ?? '';
$atlDepartementText = $donnees['atelierDepartementText'] ?? '';
$atlQuartier = $donnees['atelierQuartier'] ?? '';

if (!$idArtisan || !$idCorpsMetier) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Informations professionnelles manquantes."]);
    exit;
}

// Fonction interne d'appel à OpenStreetMap (Nominatim)
function getCoords($quartier, $arr, $com, $dept) {
    $adresse = "$quartier, $arr, $com, $dept, Bénin";
    $url = "https://openstreetmap.org" . urlencode($adresse) . "&format=json&limit=1";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "ArtiSkillsANPS/1.0");
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $reponse = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($reponse, true);
    if (!empty($data) && isset($data[0]['lat'])) {
        return [(float)$data[0]['lat'], (float)$data[0]['lon']];
    }
    
    // Sécurité (Fallback) : Si le quartier exact échoue, on cherche l'arrondissement global
    $adresseSecours = "$arr, $com, $dept, Bénin";
    $urlS = "https://openstreetmap.org" . urlencode($adresseSecours) . "&format=json&limit=1";
    $chS = curl_init();
    curl_setopt($chS, CURLOPT_URL, $urlS);
    curl_setopt($chS, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chS, CURLOPT_USERAGENT, "ArtiSkillsANPS/1.0");
    curl_setopt($chS, CURLOPT_TIMEOUT, 5);
    $reponseS = curl_exec($chS);
    curl_close($chS);
    
    $dataS = json_decode($reponseS, true);
    if (!empty($dataS) && isset($dataS[0]['lat'])) {
        return [(float)$dataS[0]['lat'], (float)$dataS[0]['lon']];
    }
    return [0.0, 0.0];
}

try {
    // 1. Mettre à jour les infos métier et expérience sur l'artisan
    $stmtArt = $pdo->prepare("UPDATE artisan SET idMetier = :idMetier, experience = :exp WHERE id_artisan = :id");
    $stmtArt->execute(['idMetier' => $idCorpsMetier, 'exp' => $anneesExperience, 'id' => $idArtisan]);

    // 2. Traitement Géographique Résidence
    $coordsRes = getCoords($resQuartier, $resArrondissementText, $resCommuneText, $resDepartementText);
    // Insertion dans votre table 'quartier_village' mise à jour
    $stmtQRes = $pdo->prepare("INSERT INTO quartier_village (nomQuartier, id_arrondissement, latitude, longitude) VALUES (:nom, :idArr, :lat, :lon) ON DUPLICATE KEY UPDATE idQuartier=LAST_INSERT_ID(idQuartier)");
    $stmtQRes->execute([
        'nom' => $resQuartier,
        'idArr' => $donnees['residenceArrondissementId'],
        'lat' => $coordsRes[0],
        'lon' => $coordsRes[1]
    ]);
    $idQRes = $pdo->lastInsertId();

    // Insertion dans l'adresse de résidence de l'artisan
    $stmtAddRes = $pdo->prepare("INSERT INTO adresse_artisan (type_adresse, id_quartier, id_artisan) VALUES ('Residence', :idQ, :idA)");
    $stmtAddRes->execute(['idQ' => $idQRes, 'idA' => $idArtisan]);

    // 3. Traitement Géographique Atelier
    $coordsAtl = getCoords($atlQuartier, $atlArrondissementText, $atlCommuneText, $atlDepartementText);
    $stmtQAtl = $pdo->prepare("INSERT INTO quartier_village (nomQuartier, id_arrondissement, latitude, longitude) VALUES (:nom, :idArr, :lat, :lon) ON DUPLICATE KEY UPDATE idQuartier=LAST_INSERT_ID(idQuartier)");
    $stmtQAtl->execute([
        'nom' => $atlQuartier,
        'idArr' => $donnees['atelierArrondissementId'],
        'lat' => $coordsAtl[0],
        'lon' => $coordsAtl[1]
    ]);
    $idQAtl = $pdo->lastInsertId();

    // Insertion dans l'adresse d'atelier de l'artisan
    $stmtAddAtl = $pdo->prepare("INSERT INTO adresse_artisan (type_adresse, id_quartier, id_artisan) VALUES ('Atelier', :idQ, :idA)");
    $stmtAddAtl->execute(['idQ' => $idQAtl, 'idA' => $idArtisan]);

    echo json_encode(["success" => true, "message" => "Profil géographique et professionnel enregistré avec succès."]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
