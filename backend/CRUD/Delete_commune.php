<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$idCommune = $donnees['idCommune'] ?? $_GET['id'] ?? null;

if (!$idCommune) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Le champ 'idCommune' est obligatoire."]);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM commune WHERE idCommune = :idCommune");
    $stmt->execute(["idCommune" => $idCommune]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Commune introuvable."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Commune supprimée avec succès."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Suppression impossible : " . $e->getMessage()]);
}