<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

try {
    if (isset($_GET['id'])) {
        // Récupère la question avec ses réponses associées
        $stmt = $pdo->prepare("
            SELECT q.idQuestion, q.enonce, q.typeQuestion, q.code_corpsmetier, cm.libelle AS libelleMetier
            FROM question q JOIN corps_metier cm ON cm.code = q.code_corpsmetier
            WHERE q.idQuestion = :id
        ");
        $stmt->execute(["id" => $_GET['id']]);
        $question = $stmt->fetch();

        if (!$question) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Question introuvable."]);
            exit;
        }

        $stmtReponses = $pdo->prepare("SELECT idReponse, libelleReponse, estCorrecte FROM reponse WHERE idQuestion = :id");
        $stmtReponses->execute(["id" => $_GET['id']]);
        $question['reponses'] = $stmtReponses->fetchAll();

        echo json_encode(["success" => true, "data" => $question]);
    } elseif (isset($_GET['code_corpsmetier'])) {
        $stmt = $pdo->prepare("
            SELECT idQuestion, enonce, typeQuestion, code_corpsmetier
            FROM question WHERE code_corpsmetier = :code_corpsmetier
            ORDER BY idQuestion DESC
        ");
        $stmt->execute(["code_corpsmetier" => $_GET['code_corpsmetier']]);
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    } else {
        $stmt = $pdo->query("
            SELECT q.idQuestion, q.enonce, q.typeQuestion, q.code_corpsmetier, cm.libelle AS libelleMetier
            FROM question q JOIN corps_metier cm ON cm.code = q.code_corpsmetier
            ORDER BY q.idQuestion DESC
        ");
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la lecture : " . $e->getMessage()]);
}