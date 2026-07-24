<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

$idCommune = $_GET['idCommune'] ?? null;

if (!$idCommune) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "idCommune manquant"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_arrondissement, nom_arrondissement FROM arrondissement WHERE idCommune = :id ORDER BY nom_arrondissement ASC");
    $stmt->execute(['id' => $idCommune]);
    $arrondissements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "arrondissements" => $arrondissements
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>