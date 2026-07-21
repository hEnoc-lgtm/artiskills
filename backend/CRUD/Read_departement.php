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
        $stmt = $pdo->prepare("SELECT idDepart, nomDepartement FROM departement WHERE idDepart = :id");
        $stmt->execute(["id" => $_GET['id']]);
        $departement = $stmt->fetch();

        if (!$departement) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Département introuvable."]);
            exit;
        }

        echo json_encode(["success" => true, "data" => $departement]);
    } else {
        $stmt = $pdo->query("SELECT idDepart, nomDepartement FROM departement ORDER BY nomDepartement ASC");
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la lecture : " . $e->getMessage()]);
}