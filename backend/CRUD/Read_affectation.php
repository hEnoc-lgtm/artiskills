<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

// Récupérer l'idTest si fourni (pour la vue artisan)
$idTest = $_GET['idTest'] ?? null;

try {
    // Requête avec JOIN pour récupérer toutes les infos nécessaires en une seule fois
    $sql = "
        SELECT 
            t.idTest,
            ar.npi,
            ar.nom,
            ar.prenom,
            t.score AS note_test,
            c.nomCentre AS centre_attribue,
            a.distanceCalculee,
            a.dateAffectation,
            a.statutPlace
        FROM affectation a
        JOIN test t ON a.idTest = t.idTest
        JOIN artisan ar ON t.id_artisan = ar.id_artisan
        LEFT JOIN centre_formation c ON a.idCentre = c.idCentre
    ";

    $params = [];

    // Si on demande un test spécifique, on ajoute la condition WHERE
    if ($idTest) {
        $sql .= " WHERE t.idTest = :idTest";
        $params[':idTest'] = $idTest;
    }

    $sql .= " ORDER BY a.dateAffectation DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erreur de base de données : " . $e->getMessage()
    ]);
}
?>