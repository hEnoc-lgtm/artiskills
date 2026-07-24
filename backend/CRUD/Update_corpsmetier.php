<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$code_corpsmetier = $donnees['code_corpsmetier'] ?? null;
$libelle = trim($donnees['libelle'] ?? '');

if (!$code_corpsmetier || $libelle === '') {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Les champs 'code_corpsmetier' et 'libelle' sont obligatoires."]);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE corps_metier SET libelle = :libelle WHERE code_corpsmetier = :code");
    $stmt->execute(["libelle" => $libelle, "code" => $code_corpsmetier]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Corps de métier introuvable ou aucune modification effectuée."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Corps de métier mis à jour avec succès."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la mise à jour : " . $e->getMessage()]);
}
