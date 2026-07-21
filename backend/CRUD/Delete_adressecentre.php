<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$idAdresse = $donnees['idAdresse'] ?? $_GET['id'] ?? null;

if (!$idAdresse) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Le champ 'idAdresse' est obligatoire."]);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM adresse_centre WHERE idAdresse = :idAdresse");
    $stmt->execute(["idAdresse" => $idAdresse]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Adresse de centre introuvable."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Adresse de centre supprimée avec succès."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Suppression impossible : " . $e->getMessage()]);
}