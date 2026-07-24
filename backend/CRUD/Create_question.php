<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée. Utilisez POST."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);

// 1. Validation de la Question
$enonce = trim($donnees['enonce'] ?? '');
$typeQuestion = $donnees['typeQuestion'] ?? 'QCM_unique';
$code_corpsmetier = trim($donnees['code_corpsmetier'] ?? '');
$optionsReponses = $donnees['options'] ?? []; // Tableau attendu : [ {libelle: "...", estCorrecte: 0/1}, ... ]

if ($enonce === '' || $code_corpsmetier === '') {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "L'énoncé de la question et le corps de métier sont obligatoires."]);
    exit;
}

if (count($optionsReponses) < 2) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Vous devez fournir au moins 2 options de réponse pour le QCM."]);
    exit;
}

try {
    // Début de la Transaction Sécurisée
    $pdo->beginTransaction();

    // A. Insertion de la question principale
    $stmtQ = $pdo->prepare("
        INSERT INTO question (enonce, typeQuestion, code_corpsmetier) 
        VALUES (:enonce, :typeQ, :metier)
    ");
    $stmtQ->execute([
        "enonce" => $enonce,
        "typeQ" => $typeQuestion,
        "metier" => $code_corpsmetier
    ]);
    
    // Récupération de l'identifiant de la question venant d'être créée
    $idQuestionGeneree = (int)$pdo->lastInsertId();

    // B. Insertion en boucle des options de réponses rattachées à cet idQuestion
    $stmtR = $pdo->prepare("
        INSERT INTO reponse (libelleReponse, estCorrecte, idQuestion) 
        VALUES (:libelle, :correct, :idQ)
    ");

    foreach ($optionsReponses as $opt) {
        $libelleOpt = trim($opt['libelleReponse'] ?? '');
        $estCorrecteOpt = isset($opt['estCorrecte']) && $opt['estCorrecte'] == 1 ? 1 : 0;

        if ($libelleOpt !== '') {
            $stmtR->execute([
                "libelle" => $libelleOpt,
                "correct" => $estCorrecteOpt,
                "idQ" => $idQuestionGeneree
            ]);
        }
    }

    // Si tout s'est bien passé, on valide définitivement l'écriture dans MySQL
    $pdo->commit();

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Question et options de réponses QCM enregistrées simultanément avec succès."
    ]);

} catch (PDOException $e) {
    // En cas de plantage, on annule toutes les modifications de la transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur de double insertion : " . $e->getMessage()]);
}
