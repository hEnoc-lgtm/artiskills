<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$idDepart = $donnees['idDepart'] ?? $_GET['id'] ?? null;

if (!$idDepart) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Le champ 'idDepart' est obligatoire."]);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM departement WHERE idDepart = :idDepart");
    $stmt->execute(["idDepart" => $idDepart]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Département introuvable."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Département supprimé avec succès."]);
} catch (PDOException $e) {
    // Erreur typique : ON DELETE RESTRICT car des communes existent encore pour ce département
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Suppression impossible : " . $e->getMessage()]);
}