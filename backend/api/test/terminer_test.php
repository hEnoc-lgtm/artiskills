<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/osm.php'; // ← NOUVEAU : import des fonctions OSM

$idTest = $_GET['idTest'] ?? null;

if (!$idTest) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "ID de test manquant."]);
    exit;
}

try {
    // 1. Calcul du score (sur 10)
    $stmtScore = $pdo->prepare("
        SELECT COUNT(*) as nbCorrectes 
        FROM question_test 
        WHERE idTest = :idTest AND estcorrecte = 1
    ");
    $stmtScore->execute(['idTest' => $idTest]);
    $resultat = $stmtScore->fetch(PDO::FETCH_ASSOC);
    $score = $resultat['nbCorrectes'];

    // 2. Mise à jour du test
    $stmt = $pdo->prepare("
        UPDATE test 
        SET heureFin = CURTIME(), 
            statutTest = 'termine',
            score = :score
        WHERE idTest = :idTest
    ");
    $stmt->execute([
        'score' => $score,
        'idTest' => $idTest
    ]);

    // 3. Calcul de la durée
    $stmtTime = $pdo->prepare("SELECT heureDebut, heureFin FROM test WHERE idTest = :idTest");
    $stmtTime->execute(['idTest' => $idTest]);
    $times = $stmtTime->fetch(PDO::FETCH_ASSOC);

    $dureeSecondes = 0;
    $dureeFormatee = "00:00";

    if ($times && $times['heureDebut'] && $times['heureFin']) {
        $debut = strtotime($times['heureDebut']);
        $fin = strtotime($times['heureFin']);
        $dureeSecondes = max(0, $fin - $debut);
        $minutes = floor($dureeSecondes / 60);
        $secondes = $dureeSecondes % 60;
        $dureeFormatee = sprintf("%02d:%02d", $minutes, $secondes);
    }

    // ═══════════════════════════════════════════════════════════════
    // 📍 AFFECTATION AUTOMATIQUE AU CENTRE LE PLUS PROCHE
    // ═══════════════════════════════════════════════════════════════
    $resultatAffectation = affecterAuCentreLePlusProche($pdo, $idTest);
    // ═══════════════════════════════════════════════════════════════

    echo json_encode([
        "success" => true,
        "message" => "Test terminé et enregistré avec succès.",
        "duree" => $dureeFormatee,
        "dureeSecondes" => $dureeSecondes,
        "score" => $score,
        "affectation" => $resultatAffectation // ← info sur l'affectation (centre, distance, statut)
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur PDO : " . $e->getMessage()]);
}
?>