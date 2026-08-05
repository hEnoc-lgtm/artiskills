<?php
try {
    // 1. Calcul de la note sur 20
    $stmtScore = $pdo->prepare("
        SELECT
            COALESCE(SUM(CASE WHEN estcorrecte = 1 THEN 1 ELSE 0 END), 0) AS bonnes_reponses
        FROM question_test
        WHERE idTest = :idTest
    ");
    $stmtScore->execute(['idTest' => $idTest]);
    $bonnesReponses = (int) $stmtScore->fetchColumn();

    $note = (int) round($bonnesReponses * 2);
    $note = max(0, min(20, $note));

    // 2. Mise à jour finale du test
    $stmt = $pdo->prepare("
        UPDATE test
        SET heureFin = CURTIME(),
            note = :note,
            score = :note,
            statutTest = 'termine'
        WHERE idTest = :idTest
    ");
    $stmt->execute([
        'note' => $note,
        'idTest' => $idTest
    ]);

    // 3. Durée réelle
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

    echo json_encode([
        "success" => true,
        "message" => "Test terminé et enregistré avec succès.",
        "duree" => $dureeFormatee,
        "dureeSecondes" => $dureeSecondes,
        "note" => $note
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur PDO : " . $e->getMessage()]);
}