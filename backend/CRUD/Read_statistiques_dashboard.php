<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

try {
    // 1. Nombre total de profils d'agents enregistrés
    $stmt1 = $pdo->query("SELECT COUNT(*) FROM profil");
    $totalAgents = (int)$stmt1->fetchColumn();

    // 2. Nombre total de tests d'évaluation initiés
    $stmt2 = $pdo->query("
        SELECT COUNT(*)
        FROM test
        WHERE date IS NOT NULL AND heureDebut IS NOT NULL
    ");
    $totalTests = (int)$stmt2->fetchColumn();

    // 3. Nombre de tests validés (score >= 10/20)
    $stmt3 = $pdo->query("
        SELECT COUNT(*)
        FROM test
        WHERE score >= 10
    ");
    $testsValides = (int)$stmt3->fetchColumn();

    // Taux global
    $tauxReussite = $totalTests > 0 ? round(($testsValides / $totalTests) * 100, 1) : 0;

    // 4. Répartition géographique
    $stmt4 = $pdo->query("
        SELECT
            IFNULL(d.nom_departement, 'Non localisé') AS departement,
            COUNT(t.idTest) AS total_evaluations,
            SUM(CASE WHEN t.score >= 10 THEN 1 ELSE 0 END) AS total_valides
        FROM test t
        JOIN artisan a ON t.id_artisan = a.id_artisan
        LEFT JOIN quartier_village qv ON a.id_quartier_residence = qv.id_quartier
        LEFT JOIN arrondissement arr ON qv.id_arrondissement = arr.id_arrondissement
        LEFT JOIN commune c ON arr.id_commune = c.id_commune
        LEFT JOIN departement d ON c.id_departement = d.id_departement
        WHERE t.date IS NOT NULL AND t.heureDebut IS NOT NULL
        GROUP BY d.id_departement
        ORDER BY total_evaluations DESC
    ");
    $repartitionGeo = $stmt4->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "compteurs" => [
            "totalAgents" => $totalAgents,
            "totalTests" => $totalTests,
            "tauxReussite" => $tauxReussite . "%"
        ],
        "geographie" => $repartitionGeo
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
?>