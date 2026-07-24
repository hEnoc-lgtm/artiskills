<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$idQuestion = $donnees['idQuestion'] ?? null;
$enonce = trim($donnees['enonce'] ?? '');
$typeQuestion = $donnees['typeQuestion'] ?? 'QCM_unique';
$code_corpsmetier = trim($donnees['code_corpsmetier'] ?? '');
$options = $donnees['options'] ?? [];

if (!$idQuestion || $enonce === '' || $code_corpsmetier === '') {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Données incomplètes pour la modification."]);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Mise à jour de la question principale
    $stmtQ = $pdo->prepare("
        UPDATE question 
        SET enonce = :enonce, typeQuestion = :typeQ, code_corpsmetier = :metier
        WHERE idQuestion = :id
    ");
    $stmtQ->execute([
        "enonce" => $enonce,
        "typeQ" => $typeQuestion,
        "metier" => $code_corpsmetier,
        "id" => $idQuestion
    ]);

    // 2. Mise à jour des réponses
    // Pour simplifier et éviter les conflits d'IDs, on supprime les anciennes options et on réinsère les nouvelles
    $stmtDelRep = $pdo->prepare("DELETE FROM reponse WHERE idQuestion = :id");
    $stmtDelRep->execute(['id' => $idQuestion]);

    $stmtInsRep = $pdo->prepare("
        INSERT INTO reponse (libelleReponse, estCorrecte, idQuestion) 
        VALUES (:libelle, :correct, :idQ)
    ");

    foreach ($options as $opt) {
        $libelleOpt = trim($opt['libelleReponse'] ?? '');
        $estCorrecteOpt = isset($opt['estCorrecte']) && $opt['estCorrecte'] == 1 ? 1 : 0;

        if ($libelleOpt !== '') {
            $stmtInsRep->execute([
                "libelle" => $libelleOpt,
                "correct" => $estCorrecteOpt,
                "idQ" => $idQuestion
            ]);
        }
    }

    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Question et options QCM modifiées avec succès."]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur de modification : " . $e->getMessage()]);
}
