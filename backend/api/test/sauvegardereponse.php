<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

// 1. Récupérer les données JSON envoyées par le frontend
$data = json_decode(file_get_contents("php://input"), true);

$idTest = $data['idTest'] ?? null;
$idQuestion = $data['idQuestion'] ?? null;
$idReponse = $data['idReponse'] ?? null;
$libelleReponse = $data['libelleReponse'] ?? null;

// Vérification des données obligatoires
if (!$idTest || !$idQuestion || !$idReponse || !$libelleReponse) {
    http_response_code(422);
    echo json_encode([
        "success" => false, 
        "message" => "Données manquantes pour l'enregistrement de la réponse."
    ]);
    exit;
}

try {
    // 2. Vérifier que le test est toujours en cours (sécurité)
    $stmtTest = $pdo->prepare("SELECT statutTest FROM test WHERE idTest = :idTest");
    $stmtTest->execute(['idTest' => $idTest]);
    $statutTest = $stmtTest->fetchColumn();

    if ($statutTest !== 'en_cours') {
        http_response_code(403);
        echo json_encode([
            "success" => false, 
            "message" => "Ce test n'est plus en cours. Impossible de sauvegarder une réponse."
        ]);
        exit;
    }

    // 3. Vérifier si la réponse choisie est la bonne
    $stmtCheckReponse = $pdo->prepare("
        SELECT estCorrecte 
        FROM reponse 
        WHERE idReponse = :idReponse AND idQuestion = :idQuestion
    ");
    $stmtCheckReponse->execute([
        'idReponse' => $idReponse,
        'idQuestion' => $idQuestion
    ]);
    $estCorrecte = $stmtCheckReponse->fetchColumn();
    
    // estCorrecte vaut 1 si c'est la bonne réponse, 0 sinon
    $estCorrecteInt = $estCorrecte ? 1 : 0;

    // 4. Mettre à jour la table question_test
    $stmtUpdate = $pdo->prepare("
        UPDATE question_test 
        SET reponseDonnee = :libelleReponse, 
            estVerouillee = 1, 
            estcorrecte = :estCorrecte
        WHERE idTest = :idTest AND idQuestion = :idQuestion
    ");
    
    $stmtUpdate->execute([
        'libelleReponse' => $libelleReponse,
        'estCorrecte' => $estCorrecteInt,
        'idTest' => $idTest,
        'idQuestion' => $idQuestion
    ]);

    // 5. Vérifier si la mise à jour a fonctionné
    if ($stmtUpdate->rowCount() > 0) {
        echo json_encode([
            "success" => true, 
            "message" => "Réponse enregistrée et verrouillée avec succès.",
            "estCorrecte" => $estCorrecteInt
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            "success" => false, 
            "message" => "Aucune question correspondante trouvée dans ce test."
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur PDO : " . $e->getMessage()]);
}
?>