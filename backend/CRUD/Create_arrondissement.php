<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$nom_arrondissement = trim($donnees['nom_arrondissement'] ?? '');
$longitude = $donnees['longitude'] ?? null;
$latitude = $donnees['latitude'] ?? null;
$idCommune = $donnees['idCommune'] ?? null;

if ($nom_arrondissement === '' || $longitude === null || $latitude === null || !$idCommune) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Les champs 'nom_arrondissement', 'longitude', 'latitude' et 'idCommune' sont obligatoires."]);
    exit;
}

try {
    $verif = $pdo->prepare("SELECT idCommune FROM commune WHERE idCommune = :idCommune");
    $verif->execute(["idCommune" => $idCommune]);
    if (!$verif->fetch()) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "La commune indiquée n'existe pas."]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO arrondissement (nom_arrondissement, longitude, latitude, idCommune)
        VALUES (:nom_arrondissement, :longitude, :latitude, :idCommune)
    ");
    $stmt->execute([
        "nom_arrondissement" => $nom_arrondissement,
        "longitude" => $longitude,
        "latitude" => $latitude,
        "idCommune" => $idCommune,
    ]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Arrondissement créé avec succès.",
        "data" => [
            "id_arrondissement" => (int) $pdo->lastInsertId(),
            "nom_arrondissement" => $nom_arrondissement,
            "longitude" => (float) $longitude,
            "latitude" => (float) $latitude,
            "idCommune" => (int) $idCommune,
        ],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création : " . $e->getMessage()]);
}