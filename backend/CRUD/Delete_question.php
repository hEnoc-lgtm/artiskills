<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$idQuestion = $donnees['idQuestion'] ?? $_GET['id'] ?? null;

if (!$idQuestion) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Le champ 'idQuestion' est obligatoire."]);
    exit;
}

try {
    // La suppression entraîne, via ON DELETE CASCADE, celle des réponses associées (table reponse).
    $stmt = $pdo->prepare("DELETE FROM question WHERE idQuestion = :idQuestion");
    $stmt->execute(["idQuestion" => $idQuestion]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Question introuvable."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Question supprimée avec succès."]);
} catch (PDOException $e) {
    // Erreur typique : la question est encore référencée dans question_test (ON UPDATE CASCADE, pas de CASCADE en delete)
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Suppression impossible : " . $e->getMessage()]);
}