<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

try {
    // Requête SQL complète pour extraire l'identité, le métier et le département d'origine
    $stmt = $pdo->query("
        SELECT 
            t.idTest,
            t.dateDebutTest,
            t.note as note_test,
            a.nom,
            a.prenom,
            a.npi,
            m.libelle as nom_metier,
            d.nom_departement,
            (SELECT COUNT(*) FROM question_test WHERE idTest = t.idTest AND estVerouillee = 1) as questions_repondues,
            TIMESTAMPDIFF(SECOND, t.dateDebutTest, NOW()) as temps_ecoule_secondes
        FROM test t
        JOIN artisan a ON t.id_artisan = a.id_artisan
        LEFT JOIN corps_metier m ON a.code_corpsmetier = m.code_corpsmetier
        -- Jointures en cascade pour remonter jusqu'au département de résidence
        LEFT JOIN quartier_village qv ON a.id_quartier_residence = qv.id_quartier
        LEFT JOIN arrondissement arr ON qv.id_arrondissement = arr.id_arrondissement
        LEFT JOIN commune c ON arr.id_commune = c.id_commune
        LEFT JOIN departement d ON c.id_departement = d.id_departement
        ORDER BY t.dateDebutTest DESC
    ");
    
    $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Détermination dynamique du statut de l'évaluation pour le tableau
    foreach ($resultats as &$r) {
        $note = $r['note_test'] !== null ? (int)$r['note_test'] : 0;
        $totalRepondu = (int)$r['questions_repondues'];
        $tempsEcoule = $r['temps_ecoule_secondes'] !== null ? (int)$r['temps_ecoule_secondes'] : 0;

        if ($r['dateDebutTest'] === null) {
            $r['statut_test'] = "Non débuté";
        } elseif ($totalRepondu === 10 || $note >= 10) {
            $r['statut_test'] = "Validé";
        } elseif ($tempsEcoule > 600) {
            $r['statut_test'] = "Expiré";
        } else {
            $r['statut_test'] = "En attente";
        }
    }

    echo json_encode(["success" => true, "data" => $resultats]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
