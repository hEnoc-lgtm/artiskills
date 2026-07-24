<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$code_corpsmetier = $donnees['code_corpsmetier'] ?? $_GET['code'] ?? null;

if (!$code_corpsmetier) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Le champ 'code_corpsmetier' est obligatoire."]);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM corps_metier WHERE code_corpsmetier = :code");
    $stmt->execute(["code" => $code_corpsmetier]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Corps de métier introuvable."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Corps de métier supprimé avec succès."]);
} catch (PDOException $e) {
    // Erreur typique : des artisans, questions ou objectifs de formation utilisent encore ce corps de métier
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Suppression impossible : " . $e->getMessage()]);
}
