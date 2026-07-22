<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$ordre = $donnees['ordre'] ?? null;
$idTest = $donnees['idTest'] ?? null;
$idQuestion = $donnees['idQuestion'] ?? null;

if ($ordre === null || !$idTest || !$idQuestion) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Les champs 'ordre', 'idTest' et 'idQuestion' sont obligatoires."]);
    exit;
}

try {
    $verifTest = $pdo->prepare("SELECT idTest FROM test WHERE idTest = :id");
    $verifTest->execute(["id" => $idTest]);
    if (!$verifTest->fetch()) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "Le test indiqué n'existe pas."]);
        exit;
    }

    $verifQuestion = $pdo->prepare("SELECT idQuestion FROM question WHERE idQuestion = :id");
    $verifQuestion->execute(["id" => $idQuestion]);
    if (!$verifQuestion->fetch()) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "La question indiquée n'existe pas."]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO question_test (ordre, idTest, idQuestion) VALUES (:ordre, :idTest, :idQuestion)");
    $stmt->execute(["ordre" => $ordre, "idTest" => $idTest, "idQuestion" => $idQuestion]);

    http_response_code(201);
    echo json_encode(["success" => true, "message" => "Question ajoutée au test avec succès.", "data" => ["idQuestiontest" => (int) $pdo->lastInsertId()]]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création : " . $e->getMessage()]);
}