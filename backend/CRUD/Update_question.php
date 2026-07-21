<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$idQuestion = $donnees['idQuestion'] ?? null;
$enonce = trim($donnees['enonce'] ?? '');
$typeQuestion = $donnees['typeQuestion'] ?? '';
$code_corpsmetier = $donnees['code_corpsmetier'] ?? null;

if (!$idQuestion || $enonce === '' || !$typeQuestion || !$code_corpsmetier) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Tous les champs sont obligatoires."]);
    exit;
}

if (!in_array($typeQuestion, ['QCM_unique', 'QCM_multiple', 'VraiFaux'], true)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "typeQuestion doit être 'QCM_unique', 'QCM_multiple' ou 'VraiFaux'."]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE question SET enonce = :enonce, typeQuestion = :typeQuestion, code_corpsmetier = :code_corpsmetier
        WHERE idQuestion = :idQuestion
    ");
    $stmt->execute([
        "enonce" => $enonce, "typeQuestion" => $typeQuestion,
        "code_corpsmetier" => $code_corpsmetier, "idQuestion" => $idQuestion,
    ]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Question introuvable ou aucune modification effectuée."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Question mise à jour avec succès."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la mise à jour : " . $e->getMessage()]);
}