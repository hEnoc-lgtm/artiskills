<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$code_corpsmetier = trim($donnees['code_corpsmetier'] ?? '');
$libelle = trim($donnees['libelle'] ?? '');

if ($code_corpsmetier === '' || $libelle === '') {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Les champs 'code_corpsmetier' et 'libelle' sont obligatoires."]);
    exit;
}

try {
    $verif = $pdo->prepare("SELECT code_corpsmetier FROM corps_metier WHERE code_corpsmetier = :code");
    $verif->execute(["code" => $code_corpsmetier]);
    if ($verif->fetch()) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Ce code de corps de métier existe déjà."]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO corps_metier (code_corpsmetier, libelle) VALUES (:code, :libelle)");
    $stmt->execute(["code" => $code_corpsmetier, "libelle" => $libelle]);

    http_response_code(201);
    echo json_encode(["success" => true, "message" => "Corps de métier créé avec succès.", "data" => ["code_corpsmetier" => $code_corpsmetier, "libelle" => $libelle]]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création : " . $e->getMessage()]);
}
