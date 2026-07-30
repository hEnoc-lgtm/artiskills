<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

// 1. On récupère seulement l'ID du test depuis l'URL
$idTest = $_GET['idTest'] ?? null;

if (!$idTest) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "ID de test manquant."]);
    exit;
}

try {
    // 2. RÉCUPÉRATION DU MÉTIER DEPUIS LA TABLE TEST (C'est la clé du succès !)
    $stmtMetier = $pdo->prepare("SELECT code_corpsmetier FROM test WHERE idTest = :idTest");
    $stmtMetier->execute(['idTest' => $idTest]);
    $code_corpsmetier = $stmtMetier->fetchColumn();
    
    if (!$code_corpsmetier) {
        http_response_code(404);
        echo json_encode([
            "success" => false, 
            "message" => "Aucun corps de métier associé à ce test. Veuillez compléter le prétest d'abord."
        ]);
        exit;
    }

    // 3. GESTION DU CHRONOMÈTRE
    $stmtTime = $pdo->prepare("SELECT date FROM test WHERE idTest = :idTest");
    $stmtTime->execute(['idTest' => $idTest]);
    $dateDebut = $stmtTime->fetchColumn();

    $maintenant = time();
    $dureeMaxTest = 600; // 10 minutes

    if (!$dateDebut) {
        $stmtStart = $pdo->prepare("UPDATE test SET date = NOW() WHERE idTest = :idTest");
        $stmtStart->execute(['idTest' => $idTest]);
        $tempsRestantCalcule = $dureeMaxTest; 
    } else {
        $timestampDebut = strtotime($dateDebut);
        $tempsEcoule = $maintenant - $timestampDebut;
        $tempsRestantCalcule = max(0, $dureeMaxTest - $tempsEcoule);
    }

    // 4. NETTOYAGE ANTI-TRICHE
    $stmtClean = $pdo->prepare("DELETE FROM question_test WHERE idTest = :idTest AND estVerouillee = 0");
    $stmtClean->execute(['idTest' => $idTest]);

    // 5. COMPTE DES QUESTIONS DÉJÀ RÉPONDUES
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM question_test WHERE idTest = :idTest AND estVerouillee = 1");
    $stmtCount->execute(['idTest' => $idTest]);
    $questionsVerrouillees = $stmtCount->fetchColumn();
    $questionsManquantes = 10 - $questionsVerrouillees;

    // 6. RÉCUPÉRATION DES NOUVELLES QUESTIONS
    if ($questionsManquantes > 0 && $tempsRestantCalcule > 0) {
        $stmtNew = $pdo->prepare("
            SELECT idQuestion FROM question 
            WHERE code_corpsmetier = :code_corpsmetier 
            AND idQuestion NOT IN (SELECT idQuestion FROM question_test WHERE idTest = :idTest)
            ORDER BY RAND() 
            LIMIT :limite
        ");
        
        $stmtNew->bindValue(':code_corpsmetier', $code_corpsmetier, PDO::PARAM_STR);
        $stmtNew->bindValue(':idTest', $idTest, PDO::PARAM_INT);
        $stmtNew->bindValue(':limite', $questionsManquantes, PDO::PARAM_INT);
        $stmtNew->execute();
        $nouvellesQuestions = $stmtNew->fetchAll(PDO::FETCH_ASSOC);

        $ordreActuel = $questionsVerrouillees + 1;
        foreach ($nouvellesQuestions as $q) {
            $stmtInsert = $pdo->prepare("
                INSERT INTO question_test (ordre, idTest, idQuestion, estVerouillee, estcorrecte) 
                VALUES (:ordre, :idTest, :idQuestion, 0, 0)
            ");
            $stmtInsert->execute([
                'ordre' => $ordreActuel, 'idTest' => $idTest, 'idQuestion' => $q['idQuestion']
            ]);
            $ordreActuel++;
        }
    }

    // 7. EXTRACTION FINALE POUR L'AFFICHAGE
    $stmtFinal = $pdo->prepare("
        SELECT qt.ordre, qt.idQuestion, q.enonce, qt.estVerouillee, qt.reponseDonnee
        FROM question_test qt
        JOIN question q ON qt.idQuestion = q.idQuestion
        WHERE qt.idTest = :idTest
        ORDER BY qt.ordre ASC
    ");
    $stmtFinal->execute(['idTest' => $idTest]);
    $listeQuestions = $stmtFinal->fetchAll(PDO::FETCH_ASSOC);

    foreach ($listeQuestions as &$q) {
        $stmtRep = $pdo->prepare("SELECT idReponse, enonce FROM reponse WHERE idQuestion = :idQ");
        $stmtRep->execute(['idQ' => $q['idQuestion']]);
        $q['options'] = $stmtRep->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        "success" => true, 
        "questions" => $listeQuestions,
        "tempsRestant" => (int) $tempsRestantCalcule
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur PDO : " . $e->getMessage()]);
}
?>