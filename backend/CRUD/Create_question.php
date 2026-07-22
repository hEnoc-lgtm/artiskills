<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$enonce = trim($donnees['enonce'] ?? '');
$typeQuestion = $donnees['typeQuestion'] ?? '';
$code_corpsmetier = $donnees['code_corpsmetier'] ?? '';

if ($enonce === '' || $typeQuestion === '' || $code_corpsmetier === '') {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Les champs 'enonce', 'typeQuestion' et 'code_corpsmetier' sont obligatoires."]);
    exit;
}

if (!in_array($typeQuestion, ['QCM_unique', 'QCM_multiple', 'VraiFaux'], true)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "typeQuestion doit être 'QCM_unique', 'QCM_multiple' ou 'VraiFaux'."]);
    exit;
}

try {
    $verif = $pdo->prepare("SELECT code_corpsmetier FROM corps_metier WHERE code_corpsmetier = :code");
    $verif->execute(["code" => $code_corpsmetier]);
    if (!$verif->fetch()) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "Le corps de métier indiqué n'existe pas."]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO question (enonce, typeQuestion, code_corpsmetier) VALUES (:enonce, :typeQuestion, :code_corpsmetier)");
    $stmt->execute(["enonce" => $enonce, "typeQuestion" => $typeQuestion, "code_corpsmetier" => $code_corpsmetier]);

    http_response_code(201);
    echo json_encode(["success" => true, "message" => "Question créée avec succès.", "data" => ["idQuestion" => (int) $pdo->lastInsertId()]]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création : " . $e->getMessage()]);
}