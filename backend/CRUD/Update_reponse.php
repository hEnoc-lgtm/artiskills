<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$idReponse = $donnees['idReponse'] ?? null;
$libelleReponse = trim($donnees['libelleReponse'] ?? '');
$estCorrecte = isset($donnees['estCorrecte']) ? (bool) $donnees['estCorrecte'] : false;

if (!$idReponse || $libelleReponse === '') {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Les champs 'idReponse' et 'libelleReponse' sont obligatoires."]);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE reponse SET libelleReponse = :libelleReponse, estCorrecte = :estCorrecte WHERE idReponse = :idReponse");
    $stmt->execute(["libelleReponse" => $libelleReponse, "estCorrecte" => $estCorrecte ? 1 : 0, "idReponse" => $idReponse]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Réponse introuvable ou aucune modification effectuée."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Réponse mise à jour avec succès."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la mise à jour : " . $e->getMessage()]);
}