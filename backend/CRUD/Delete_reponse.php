<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$idReponse = $donnees['idReponse'] ?? $_GET['id'] ?? null;

if (!$idReponse) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Le champ 'idReponse' est obligatoire."]);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM reponse WHERE idReponse = :idReponse");
    $stmt->execute(["idReponse" => $idReponse]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Réponse introuvable."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Réponse supprimée avec succès."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Suppression impossible : " . $e->getMessage()]);
}
