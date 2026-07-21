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
        $stmt = $pdo->prepare("SELECT idCentre, nomCentre, contactCentre FROM centre_formation WHERE idCentre = :id");
        $stmt->execute(["id" => $_GET['id']]);
        $centre = $stmt->fetch();

        if (!$centre) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Centre de formation introuvable."]);
            exit;
        }

        echo json_encode(["success" => true, "data" => $centre]);
    } else {
        $stmt = $pdo->query("SELECT idCentre, nomCentre, contactCentre FROM centre_formation ORDER BY nomCentre ASC");
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la lecture : " . $e->getMessage()]);
}