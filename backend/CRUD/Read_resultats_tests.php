<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

try {
    $stmt = $pdo->query("
        SELECT 
            t.idTest,
            t.date,
            t.heureDebut,
            t.score,
            a.nom,
            a.prenom,
            a.npi,
            m.libelle,
            d.nomDepartement,
            (SELECT COUNT(*) FROM question_test WHERE idTest = t.idTest AND estVerouillee = 1) as questions_repondues,
            TIMESTAMPDIFF(SECOND, ADDTIME(t.date, t.heureDebut), NOW()) as temps_ecoule_secondes
        FROM test t
        JOIN artisan a ON t.id_artisan = a.id_artisan
        LEFT JOIN corps_metier m ON a.code_corpsmetier = m.code_corpsmetier
        LEFT JOIN quartier_village qv ON a.id_quartier_residence = qv.id_quartier
        LEFT JOIN arrondissement arr ON qv.id_arrondissement = arr.id_arrondissement
        LEFT JOIN commune c ON arr.idCommune = c.idCommune
        LEFT JOIN departement d ON c.idDepart = d.idDepart
        ORDER BY t.date DESC, t.heureDebut DESC
    "); 

    $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Détermination dynamique du statut de l'évaluation pour le tableau
    foreach ($resultats as &$r) {
        $note = $r['score'] !== null ? (int)$r['score'] : 0;
        $totalRepondu = (int)$r['questions_repondues'];
        $tempsEcoule = $r['temps_ecoule_secondes'] !== null ? (int)$r['temps_ecoule_secondes'] : 0;

        if ($r['date'] === null || $r['heureDebut'] === null) {
            $r['statutTest'] = "Non débuté";
        } elseif ($totalRepondu === 10 || $note >= 5) {
            $r['statutTest'] = "Validé";
        } elseif ($tempsEcoule > 600) {
            $r['statutTest'] = "Expiré";
        } else {
            $r['statutTest'] = "En attente";
        }
    }

    echo json_encode(["success" => true, "data" => $resultats]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
?>