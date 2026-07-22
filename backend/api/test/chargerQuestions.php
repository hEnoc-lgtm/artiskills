<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

$idTest = $_GET['idTest'] ?? null;
$idMetier = $_GET['idMetier'] ?? null;

if (!$idTest || !$idMetier) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Paramètres manquants."]);
    exit;
}

try {
    // =====================================================
    // SECURITY : PERSISTENT TIMEOUT MANAGEMENT
    // =====================================================
    $stmtTime = $pdo->prepare("SELECT dateDebutTest FROM test WHERE idTest = :idTest");
    $stmtTime->execute(['idTest' => $idTest]);
    $dateDebut = $stmtTime->fetchColumn();

    $maintenant = time(); // Epoch timestamp actuel du serveur en secondes
    $dureeMaxTest = 600;  // 10 minutes accordées (600 secondes)

    if (!$dateDebut) {
        // Premier lancement du test : enregistrement de l'heure exacte de départ
        $stmtStart = $pdo->prepare("UPDATE test SET dateDebutTest = NOW() WHERE idTest = :idTest");
        $stmtStart->execute(['idTest' => $idTest]);
        $tempsRestantCalcule = $dureeMaxTest; 
    } else {
        // Retour d'une déconnexion : calcul du temps réellement écoulé à l'extérieur
        $timestampDebut = strtotime($dateDebut);
        $tempsEcoule = $maintenant - $timestampDebut;
        
        $tempsRestantCalcule = $dureeMaxTest - $tempsEcoule;
        
        if ($tempsRestantCalcule < 0) {
            $tempsRestantCalcule = 0; // Le temps est dépassé, le React fermera l'accès
        }
    }

    // =====================================================
    // ANTI-CHEAT : CLEANING UNANSWERED QUESTIONS
    // =====================================================
    // Si l'artisan a quitté l'écran, on supprime de sa liste les questions où estVerouillee = 0
    $stmtClean = $pdo->prepare("DELETE FROM question_test WHERE idTest = :idTest AND estVerouillee = 0");
    $stmtClean->execute(['idTest' => $idTest]);

    // =====================================================
    // PROGRESSION : REGENERATING THE MISSING SLOTS
    // =====================================================
    // On compte combien de questions ont été définitivement verrouillées lors des connexions précédentes
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM question_test WHERE idTest = :idTest AND estVerouillee = 1");
    $stmtCount->execute(['idTest' => $idTest]);
    $questionsVerrouillees = $stmtCount->fetchColumn();

    $questionsManquantes = 10 - $questionsVerrouillees;

    // Si le test est incomplet et qu'il reste du temps, on pioche de nouvelles questions au hasard
    if ($questionsManquantes > 0 && $tempsRestantCalcule > 0) {
        $stmtNew = $pdo->prepare("
            SELECT idQuestion FROM question 
            WHERE idMetier = :idMetier 
            AND idQuestion NOT IN (SELECT idQuestion FROM question_test WHERE idTest = :idTest)
            ORDER BY RAND() 
            LIMIT :limite
        ");
        
        // Configuration stricte des types de données pour la clause LIMIT en PDO
        $stmtNew->bindValue(':idMetier', $idMetier, PDO::PARAM_INT);
        $stmtNew->bindValue(':idTest', $idTest, PDO::PARAM_INT);
        $stmtNew->bindValue(':limite', $questionsManquantes, PDO::PARAM_INT);
        $stmtNew->execute();
        $nouvellesQuestions = $stmtNew->fetchAll(PDO::FETCH_ASSOC);

        // Insertion séquentielle des nouvelles questions pour boucher les trous (de l'ordre 1 à 10)
        $ordreActuel = $questionsVerrouillees + 1;
        foreach ($nouvellesQuestions as $q) {
            $stmtInsert = $pdo->prepare("
                INSERT INTO question_test (ordre, idTest, idQuestion, estVerouillee, estcorrecte) 
                VALUES (:ordre, :idTest, :idQuestion, 0, 0)
            ");
            $stmtInsert->execute([
                'ordre' => $ordreActuel,
                'idTest' => $idTest,
                'idQuestion' => $q['idQuestion']
            ]);
            $ordreActuel++;
        }
    }

    // =====================================================
    // FINAL EXTRACTION : SENDING TO REACT FRONTEND
    // =====================================================
    // Récupération des 10 questions finales ordonnées de l'artisan
    $stmtFinal = $pdo->prepare("
        SELECT qt.ordre, qt.idQuestion, q.libelleQuestion, qt.estVerouillee, qt.reponseDonnee
        FROM question_test qt
        JOIN question q ON qt.idQuestion = q.idQuestion
        WHERE qt.idTest = :idTest
        ORDER BY qt.ordre ASC
    ");
    $stmtFinal->execute(['idTest' => $idTest]);
    $listeQuestions = $stmtFinal->fetchAll(PDO::FETCH_ASSOC);

    // Chargement dynamique des options de réponses QCM associées à chaque question
    foreach ($listeQuestions as &$q) {
        $stmtRep = $pdo->prepare("SELECT idReponse, libelleReponse FROM reponse WHERE idQuestion = :idQ");
        $stmtRep->execute(['idQ' => $q['idQuestion']]);
        $q['options'] = $stmtRep->fetchAll(PDO::FETCH_ASSOC);
    }

    // Envoi de la charge de données au format JSON structuré
    echo json_encode([
        "success" => true, 
        "questions" => $listeQuestions,
        "tempsRestant" => (int) $tempsRestantCalcule
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur système PDO : " . $e->getMessage()]);
}
