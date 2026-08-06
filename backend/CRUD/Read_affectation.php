<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

try {
    $stmt = $pdo->query("
        SELECT 
            af.idAffect,
            af.distanceCalculee,
            af.adresseReference,
            af.dateAffectation,
            af.statutPlace,
            t.idTest,
            t.score,
            t.statutAffectation,
            t.code_corpsmetier,
            a.id_artisan,
            a.npi,
            a.nom,
            a.prenom,
            cm.libelle AS nom_metier,
            c.idCentre,
            c.nomCentre,
            qv.nom_quartier AS quartier_centre,
            com.nomCommune,
            d.nomDepartement
        FROM affectation af
        JOIN test t ON af.idTest = t.idTest
        JOIN artisan a ON t.id_artisan = a.id_artisan
        LEFT JOIN corps_metier cm ON t.code_corpsmetier = cm.code_corpsmetier
        JOIN centre_formation c ON af.idCentre = c.idCentre
        LEFT JOIN quartier_village qv ON c.id_quartier_centre = qv.id_quartier
        LEFT JOIN arrondissement arr ON qv.id_arrondissement = arr.id_arrondissement
        LEFT JOIN commune com ON arr.idCommune = com.idCommune
        LEFT JOIN departement d ON com.idDepart = d.idDepart
        ORDER BY af.dateAffectation DESC, af.idAffect DESC
    ");

    echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
?>