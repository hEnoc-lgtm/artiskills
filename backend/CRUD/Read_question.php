<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$select = "
    SELECT q.idQuestion, q.enonce, q.typeQuestion, q.code_corpsmetier, cm.libelle AS libelleMetier
    FROM question q JOIN corps_metier cm ON cm.code_corpsmetier = q.code_corpsmetier
";

try {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare($select . " WHERE q.idQuestion = :id");
        $stmt->execute(["id" => $_GET['id']]);
        $question = $stmt->fetch();

        if (!$question) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Question introuvable."]);
            exit;
        }

        echo json_encode(["success" => true, "data" => $question]);
    } elseif (isset($_GET['code_corpsmetier'])) {
        $stmt = $pdo->prepare($select . " WHERE q.code_corpsmetier = :code ORDER BY q.idQuestion DESC");
        $stmt->execute(["code" => $_GET['code_corpsmetier']]);
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    } else {
        $stmt = $pdo->query($select . " ORDER BY q.idQuestion DESC");
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la lecture : " . $e->getMessage()]);
}