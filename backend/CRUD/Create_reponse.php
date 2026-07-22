<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$libelleReponse = trim($donnees['libelleReponse'] ?? '');
$estCorrecte = isset($donnees['estCorrecte']) ? (bool) $donnees['estCorrecte'] : false;
$idQuestion = $donnees['idQuestion'] ?? null;

if ($libelleReponse === '' || !$idQuestion) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Les champs 'libelleReponse' et 'idQuestion' sont obligatoires."]);
    exit;
}

try {
    $verif = $pdo->prepare("SELECT idQuestion FROM question WHERE idQuestion = :id");
    $verif->execute(["id" => $idQuestion]);
    if (!$verif->fetch()) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "La question indiquée n'existe pas."]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO reponse (libelleReponse, estCorrecte, idQuestion) VALUES (:libelleReponse, :estCorrecte, :idQuestion)");
    $stmt->execute(["libelleReponse" => $libelleReponse, "estCorrecte" => $estCorrecte ? 1 : 0, "idQuestion" => $idQuestion]);

    http_response_code(201);
    echo json_encode(["success" => true, "message" => "Réponse créée avec succès.", "data" => ["idReponse" => (int) $pdo->lastInsertId()]]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création : " . $e->getMessage()]);
}