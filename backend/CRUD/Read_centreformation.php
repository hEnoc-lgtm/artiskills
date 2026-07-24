<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit;
}

$id = $_GET['id'] ?? null;

try {
    if ($id) {
        // Sélection d'un seul centre spécifique
        $stmt = $pdo->prepare("SELECT * FROM centre_formation WHERE idCentre = :id");
        $stmt->execute(["id" => $id]);
        $centre = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$centre) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Centre introuvable."]);
            exit;
        }
        echo json_encode(["success" => true, "data" => $centre]);
    } else {
        // Lecture globale avec jointure sur la localisation du centre
        $stmt = $pdo->query("
            SELECT c.idCentre, c.nomCentre, c.contactCentre, q.nom_quartier as quartier_centre
            FROM centre_formation c
            LEFT JOIN quartier_village q ON c.id_quartier_centre = q.id_quartier
            ORDER BY c.idCentre DESC
        ");
        $centres = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $centres]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur de lecture : " . $e->getMessage()]);
}
