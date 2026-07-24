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
        $stmt = $pdo->prepare("SELECT * FROM artisan WHERE id_artisan = :id");
        $stmt->execute(["id" => $id]);
        $artisan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$artisan) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Artisan introuvable."]);
            exit;
        }
        echo json_encode(["success" => true, "data" => $artisan]);
    } else {
        $stmt = $pdo->query("SELECT id_artisan, npi, nom, prenom, contact FROM artisan ORDER BY id_artisan DESC");
        $artisans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $artisans]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur de lecture : " . $e->getMessage()]);
}
