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
        $stmt = $pdo->prepare("SELECT * FROM adresse_centre WHERE idAdresse = :id");
        $stmt->execute(["id" => $_GET['id']]);
        $adresse = $stmt->fetch();
    } elseif (isset($_GET['idCentre'])) {
        $stmt = $pdo->prepare("SELECT * FROM adresse_centre WHERE idCentre = :idCentre");
        $stmt->execute(["idCentre" => $_GET['idCentre']]);
        $adresse = $stmt->fetch();
    } else {
        $stmt = $pdo->query("SELECT * FROM adresse_centre ORDER BY idAdresse ASC");
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
        exit;
    }

    if (!$adresse) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Adresse de centre introuvable."]);
        exit;
    }

    echo json_encode(["success" => true, "data" => $adresse]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la lecture : " . $e->getMessage()]);
}