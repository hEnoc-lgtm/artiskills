<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$idCentre = $donnees['idCentre'] ?? $_GET['id'] ?? null;

if (!$idCentre) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Le champ 'idCentre' est obligatoire."]);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM centre_formation WHERE idCentre = :idCentre");
    $stmt->execute(["idCentre" => $idCentre]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Centre de formation introuvable."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Centre de formation supprimé avec succès."]);
} catch (PDOException $e) {
    // Erreur typique : des affectations existent encore pour ce centre
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Suppression impossible : " . $e->getMessage()]);
}