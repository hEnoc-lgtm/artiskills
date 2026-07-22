<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$select = "
    SELECT qv.id_quartier, qv.nom_quartier, qv.nom_normalise, qv.longitude, qv.latitude,
           qv.id_arrondissement, a.nom_arrondissement
    FROM quartier_village qv JOIN arrondissement a ON a.id_arrondissement = qv.id_arrondissement
";

try {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare($select . " WHERE qv.id_quartier = :id");
        $stmt->execute(["id" => $_GET['id']]);
        $quartier = $stmt->fetch();

        if (!$quartier) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Quartier/village introuvable."]);
            exit;
        }

        echo json_encode(["success" => true, "data" => $quartier]);
    } elseif (isset($_GET['id_arrondissement'])) {
        $stmt = $pdo->prepare($select . " WHERE qv.id_arrondissement = :id ORDER BY qv.nom_quartier ASC");
        $stmt->execute(["id" => $_GET['id_arrondissement']]);
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    } else {
        $stmt = $pdo->query($select . " ORDER BY a.nom_arrondissement ASC, qv.nom_quartier ASC");
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la lecture : " . $e->getMessage()]);
}