<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$id_arrondissement = $donnees['id_arrondissement'] ?? null;
$nom_arrondissement = trim($donnees['nom_arrondissement'] ?? '');
$longitude = $donnees['longitude'] ?? null;
$latitude = $donnees['latitude'] ?? null;
$idCommune = $donnees['idCommune'] ?? null;

if (!$id_arrondissement || $nom_arrondissement === '' || $longitude === null || $latitude === null || !$idCommune) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Tous les champs sont obligatoires."]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE arrondissement
        SET nom_arrondissement = :nom_arrondissement, longitude = :longitude, latitude = :latitude, idCommune = :idCommune
        WHERE id_arrondissement = :id_arrondissement
    ");
    $stmt->execute([
        "nom_arrondissement" => $nom_arrondissement,
        "longitude" => $longitude,
        "latitude" => $latitude,
        "idCommune" => $idCommune,
        "id_arrondissement" => $id_arrondissement,
    ]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Arrondissement introuvable ou aucune modification effectuée."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Arrondissement mis à jour avec succès."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la mise à jour : " . $e->getMessage()]);
}