<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$id_artisan = $donnees['id_artisan'] ?? $_GET['id'] ?? null;

if (!$id_artisan) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Le champ 'id_artisan' est obligatoire."]);
    exit;
}

try {
    // La suppression entraîne, via ON DELETE CASCADE, celle de sa résidence,
    // de son atelier éventuel et de son test associé.
    $stmt = $pdo->prepare("DELETE FROM artisan WHERE id_artisan = :id_artisan");
    $stmt->execute(["id_artisan" => $id_artisan]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Artisan introuvable."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Artisan supprimé avec succès."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Suppression impossible : " . $e->getMessage()]);
}