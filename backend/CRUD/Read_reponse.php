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
        $stmt = $pdo->prepare("SELECT idReponse, libelleReponse, estCorrecte, idQuestion FROM reponse WHERE idReponse = :id");
        $stmt->execute(["id" => $_GET['id']]);
        $reponse = $stmt->fetch();

        if (!$reponse) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Réponse introuvable."]);
            exit;
        }

        echo json_encode(["success" => true, "data" => $reponse]);
    } elseif (isset($_GET['idQuestion'])) {
        $stmt = $pdo->prepare("SELECT idReponse, libelleReponse, estCorrecte, idQuestion FROM reponse WHERE idQuestion = :idQuestion ORDER BY idReponse ASC");
        $stmt->execute(["idQuestion" => $_GET['idQuestion']]);
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    } else {
        $stmt = $pdo->query("SELECT idReponse, libelleReponse, estCorrecte, idQuestion FROM reponse ORDER BY idQuestion ASC, idReponse ASC");
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la lecture : " . $e->getMessage()]);
}