<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$id = $_GET['id'] ?? null;

try {
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM objectif_formation WHERE idObjectif = :id");
        $stmt->execute(["id" => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $data]);
    } else {
        // Lecture globale avec jointure sur les libellés de métiers
        $stmt = $pdo->query("
            SELECT o.idObjectif, o.nombrePlaces, o.periode, o.code_corpsmetier, m.libelle as nom_metier
            FROM '; . ' o
            LEFT JOIN corps_metier m ON o.code_corpsmetier = m.code_corpsmetier
            ORDER BY o.idObjectif DESC
        ");
        echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
