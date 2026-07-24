<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit;
}

try {
    // Requête globale avec jointures pour afficher des données lisibles à l'administrateur
    $stmt = $pdo->query("
        SELECT 
            aff.idTest,
            art.npi,
            art.nom,
            art.prenom,
            t.note as note_test,
            c.nomCentre as centre_attribue,
            aff.distanceCalculee,
            aff.dateAffectation
        FROM affectation aff
        JOIN test t ON aff.idTest = t.idTest
        JOIN artisan art ON t.id_artisan = art.id_artisan
        JOIN centre_formation c ON aff.idCentre = c.idCentre
        ORDER BY aff.dateAffectation DESC
    ");
    
    $affectations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "data" => $affectations]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur de lecture du registre : " . $e->getMessage()]);
}
