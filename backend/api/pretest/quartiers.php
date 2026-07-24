<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

$idArrond = $_GET['idArrond'] ?? null;

if (!$idArrond) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "idArrond manquant"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_quartier, nom_quartier FROM quartier_village WHERE id_arrondissement = :id ORDER BY nom_quartier ASC");
    $stmt->execute(['id' => $idArrond]);
    $quartiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "quartiers" => $quartiers
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>