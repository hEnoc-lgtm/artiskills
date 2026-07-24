<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

$idDepart = $_GET['idDepart'] ?? null;

if (!$idDepart) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "idDepart manquant"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT idCommune, nomCommune FROM commune WHERE idDepart = :id ORDER BY nomCommune ASC");
    $stmt->execute(['id' => $idDepart]);
    $communes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "communes" => $communes
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>