<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$nombrePlaces = $donnees['nombrePlaces'] ?? null;
$periode = trim($donnees['periode'] ?? '');
$code_corpsmetier = $donnees['code_corpsmetier'] ?? '';

if ($nombrePlaces === null || $periode === '' || $code_corpsmetier === '') {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Les champs 'nombrePlaces', 'periode' et 'code_corpsmetier' sont obligatoires."]);
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

    $stmt = $pdo->prepare("INSERT INTO objectif_formation (nombrePlaces, periode, code_corpsmetier) VALUES (:nombrePlaces, :periode, :code_corpsmetier)");
    $stmt->execute(["nombrePlaces" => $nombrePlaces, "periode" => $periode, "code_corpsmetier" => $code_corpsmetier]);

    http_response_code(201);
    echo json_encode(["success" => true, "message" => "Objectif de formation créé avec succès.", "data" => ["idObjectif" => (int) $pdo->lastInsertId()]]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création : " . $e->getMessage()]);
}