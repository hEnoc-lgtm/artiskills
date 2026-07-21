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
        $stmt = $pdo->prepare("SELECT * FROM residence WHERE idAdresse = :id");
        $stmt->execute(["id" => $_GET['id']]);
        $residence = $stmt->fetch();
    } elseif (isset($_GET['id_artisan'])) {
        $stmt = $pdo->prepare("SELECT * FROM residence WHERE id_artisan = :id_artisan");
        $stmt->execute(["id_artisan" => $_GET['id_artisan']]);
        $residence = $stmt->fetch();
    } else {
        $stmt = $pdo->query("SELECT * FROM residence ORDER BY idAdresse ASC");
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
        exit;
    }

    if (!$residence) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Résidence introuvable."]);
        exit;
    }

    echo json_encode(["success" => true, "data" => $residence]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la lecture : " . $e->getMessage()]);
}