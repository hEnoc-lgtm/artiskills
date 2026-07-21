<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

try {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("
            SELECT c.idCommune, c.nomCommune, c.idDepart, d.nomDepartement
            FROM commune c JOIN departement d ON d.idDepart = c.idDepart
            WHERE c.idCommune = :id
        ");
        $stmt->execute(["id" => $_GET['id']]);
        $commune = $stmt->fetch();

        if (!$commune) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Commune introuvable."]);
            exit;
        }

        echo json_encode(["success" => true, "data" => $commune]);
    } elseif (isset($_GET['idDepart'])) {
        $stmt = $pdo->prepare("SELECT idCommune, nomCommune, idDepart FROM commune WHERE idDepart = :idDepart ORDER BY nomCommune ASC");
        $stmt->execute(["idDepart" => $_GET['idDepart']]);
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    } else {
        $stmt = $pdo->query("
            SELECT c.idCommune, c.nomCommune, c.idDepart, d.nomDepartement
            FROM commune c JOIN departement d ON d.idDepart = c.idDepart
            ORDER BY d.nomDepartement ASC, c.nomCommune ASC
        ");
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la lecture : " . $e->getMessage()]);
}