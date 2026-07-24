<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$select = "
    SELECT qt.idQuestiontest, qt.ordre, qt.idTest, qt.idQuestion, q.enonce, q.typeQuestion
    FROM question_test qt JOIN question q ON q.idQuestion = qt.idQuestion
";

try {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare($select . " WHERE qt.idQuestiontest = :id");
        $stmt->execute(["id" => $_GET['id']]);
        $questionTest = $stmt->fetch();

        if (!$questionTest) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Question de test introuvable."]);
            exit;
        }

        echo json_encode(["success" => true, "data" => $questionTest]);
    } elseif (isset($_GET['idTest'])) {
        $stmt = $pdo->prepare($select . " WHERE qt.idTest = :idTest ORDER BY qt.ordre ASC");
        $stmt->execute(["idTest" => $_GET['idTest']]);
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    } else {
        $stmt = $pdo->query($select . " ORDER BY qt.idTest ASC, qt.ordre ASC");
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la lecture : " . $e->getMessage()]);
}
