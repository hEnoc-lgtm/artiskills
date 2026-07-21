<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

try {
    if (isset($_GET['code'])) {
        $stmt = $pdo->prepare("SELECT code, libelle FROM corps_metier WHERE code = :code");
        $stmt->execute(["code" => $_GET['code']]);
        $metier = $stmt->fetch();

        if (!$metier) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Corps de métier introuvable."]);
            exit;
        }

        echo json_encode(["success" => true, "data" => $metier]);
    } else {
        $stmt = $pdo->query("SELECT code, libelle FROM corps_metier ORDER BY libelle ASC");
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la lecture : " . $e->getMessage()]);
}