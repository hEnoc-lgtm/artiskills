<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$idObjectif = $donnees['idObjectif'] ?? null;
$nombrePlaces = $donnees['nombrePlaces'] ?? null;
$periode = trim($donnees['periode'] ?? '');
$code_corpsmetier = $donnees['code_corpsmetier'] ?? '';

if (!$idObjectif || $nombrePlaces === null || $periode === '' || $code_corpsmetier === '') {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Tous les champs sont obligatoires."]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE objectif_formation SET nombrePlaces = :nombrePlaces, periode = :periode, code_corpsmetier = :code_corpsmetier
        WHERE idObjectif = :idObjectif
    ");
    $stmt->execute([
        "nombrePlaces" => $nombrePlaces, "periode" => $periode,
        "code_corpsmetier" => $code_corpsmetier, "idObjectif" => $idObjectif,
    ]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Objectif de formation introuvable ou aucune modification effectuée."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Objectif de formation mis à jour avec succès."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la mise à jour : " . $e->getMessage()]);
}