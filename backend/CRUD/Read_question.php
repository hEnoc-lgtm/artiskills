<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit;
}

$id = $_GET['id'] ?? null;

try {
    if ($id) {
        // Lecture d'une seule question avec ses options associées
        $stmt = $pdo->prepare("SELECT * FROM question WHERE idQuestion = :id");
        $stmt->execute(["id" => $id]);
        $question = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($question) {
            $stmtR = $pdo->prepare("SELECT idReponse, libelleReponse, estCorrecte FROM reponse WHERE idQuestion = :idQ");
            $stmtR->execute(["idQ" => $id]);
            $question['options'] = $stmtR->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode(["success" => true, "data" => $question]);
    } else {
        // Lecture de la liste globale
        $stmt = $pdo->query("
            SELECT q.*, m.libelle as nom_metier 
            FROM question q
            LEFT JOIN corps_metier m ON q.code_corpsmetier = m.code_corpsmetier
            ORDER BY q.idQuestion DESC
        ");
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pour chaque question de la liste, on va lui rattacher ses options
        foreach ($questions as &$q) {
            $stmtR = $pdo->prepare("SELECT idReponse, libelleReponse, estCorrecte FROM reponse WHERE idQuestion = :idQ");
            $stmtR->execute(["idQ" => $q['idQuestion']]);
            $q['options'] = $stmtR->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode(["success" => true, "data" => $questions]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur de lecture : " . $e->getMessage()]);
}
