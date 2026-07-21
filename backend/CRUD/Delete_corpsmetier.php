<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$code = $donnees['code'] ?? $_GET['code'] ?? null;

if (!$code) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Le champ 'code' est obligatoire."]);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM corps_metier WHERE code = :code");
    $stmt->execute(["code" => $code]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Corps de métier introuvable."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Corps de métier supprimé avec succès."]);
} catch (PDOException $e) {
    // Erreur typique : des artisans/questions utilisent encore ce corps de métier (ON DELETE RESTRICT)
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Suppression impossible : " . $e->getMessage()]);
}