<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/osm.php';

$idTest = $_GET['idTest'] ?? null;

if (!$idTest) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "ID de test manquant."]);
    exit;
}

try {
    // 1. Récupérer le test et le quartier de résidence de l'artisan
    $stmt = $pdo->prepare("
        SELECT t.idTest, t.score, t.statutAffectation, t.code_corpsmetier,
               a.id_quartier_residence, qv.nom_quartier
        FROM test t
        JOIN artisan a ON t.id_artisan = a.id_artisan
        LEFT JOIN quartier_village qv ON a.id_quartier_residence = qv.id_quartier
        WHERE t.idTest = :idTest
    ");
    $stmt->execute(['idTest' => $idTest]);
    $test = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$test) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Test introuvable."]);
        exit;
    }

    // 2. Déjà traité ?
    if (in_array($test['statutAffectation'], ['valide', 'rejete'])) {
        echo json_encode(["success" => true, "message" => "Affectation déjà traitée."]);
        exit;
    }

    // 3. Échec au test (< 5/10)
    if ((int)$test['score'] < 5) {
        $pdo->prepare("UPDATE test SET statutAffectation = 'rejete' WHERE idTest = ?")->execute([$idTest]);
        echo json_encode(["success" => true, "message" => "Score insuffisant.", "statut" => "rejete"]);
        exit;
    }

    if (!$test['id_quartier_residence']) {
        echo json_encode(["success" => false, "message" => "Quartier de résidence manquant."]);
        exit;
    }

    // 4. GPS de l'artisan
    $coordsArtisan = obtenirCoordonneesQuartier($pdo, $test['id_quartier_residence']);
    if (!$coordsArtisan) {
        echo json_encode(["success" => false, "message" => "Impossible de localiser la résidence."]);
        exit;
    }

    // 5. Trouver le centre le plus proche
    $centres = $pdo->query("SELECT idCentre, nomCentre, id_quartier_centre FROM centre_formation")->fetchAll(PDO::FETCH_ASSOC);
    $meilleur = null;
    $distMin = INF;

    foreach ($centres as $c) {
        $coords = obtenirCoordonneesQuartier($pdo, $c['id_quartier_centre']);
        if (!$coords) continue;
        $d = distanceKm($coordsArtisan['lat'], $coordsArtisan['lon'], $coords['lat'], $coords['lon']);
        if ($d < $distMin) {
            $distMin = $d;
            $meilleur = $c;
        }
    }

    if (!$meilleur) {
        echo json_encode(["success" => false, "message" => "Aucun centre disponible."]);
        exit;
    }

    // 6. Vérifier les places (objectif_formation)
    $stmtObj = $pdo->prepare("SELECT nombrePlaces FROM objectif_formation WHERE code_corpsmetier = ? ORDER BY idObjectif DESC LIMIT 1");
    $stmtObj->execute([$test['code_corpsmetier']]);
    $objectif = $stmtObj->fetch(PDO::FETCH_ASSOC);

    $placesPrises = 0;
    if ($objectif) {
        $stmtCount = $pdo->prepare("
            SELECT COUNT(*) FROM affectation af
            JOIN test t ON af.idTest = t.idTest
            WHERE t.code_corpsmetier = ? AND af.statutPlace = 'validée directement'
        ");
        $stmtCount->execute([$test['code_corpsmetier']]);
        $placesPrises = (int)$stmtCount->fetchColumn();
    }

    $placeDispo   = !$objectif || $placesPrises < (int)$objectif['nombrePlaces'];
    $statutPlace  = $placeDispo ? 'validée directement' : 'liste_attente';
    $statutAffect = $placeDispo ? 'valide' : 'en_attente';

    // 7. Insertion / Mise à jour
    $adresseRef = "Résidence : " . ($test['nom_quartier'] ?? "inconnu") . " — " . round($distMin, 2) . " km";

    $pdo->beginTransaction();

    $stmtExist = $pdo->prepare("SELECT idAffect FROM affectation WHERE idTest = ?");
    $stmtExist->execute([$idTest]);
    $idAffect = $stmtExist->fetchColumn();

    if ($idAffect) {
        $pdo->prepare("UPDATE affectation SET distanceCalculee = ?, adresseReference = ?, statutPlace = ?, idCentre = ? WHERE idAffect = ?")
            ->execute([round($distMin, 2), $adresseRef, $statutPlace, $meilleur['idCentre'], $idAffect]);
    } else {
        $pdo->prepare("INSERT INTO affectation (distanceCalculee, adresseReference, statutPlace, idTest, idCentre) VALUES (?, ?, ?, ?, ?)")
            ->execute([round($distMin, 2), $adresseRef, $statutPlace, $idTest, $meilleur['idCentre']]);
    }

    $pdo->prepare("UPDATE test SET statutAffectation = ? WHERE idTest = ?")->execute([$statutAffect, $idTest]);
    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => $placeDispo ? "Affecté au centre le plus proche." : "Placé en liste d'attente.",
        "centre" => $meilleur['nomCentre'],
        "distance_km" => round($distMin, 2),
        "statutPlace" => $statutPlace
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur PDO : " . $e->getMessage()]);
}
?>