<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

$donnees = json_decode(file_get_contents("php://input"), true);
$id = $donnees['idObjectif'] ?? null;

try {
    $stmt = $pdo->prepare("DELETE FROM '; . ' WHERE idObjectif = :id");
    $stmt->execute(['id' => $id]);
    echo json_encode(["success" => true, "message" => "Objectif de quotas supprimé du registre."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
