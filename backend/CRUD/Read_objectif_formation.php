<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$select = "
    SELECT o.idObjectif, o.nombrePlaces, o.periode, o.code_corpsmetier, cm.libelle AS libelleMetier
    FROM objectif_formation o JOIN corps_metier cm ON cm.code_corpsmetier = o.code_corpsmetier
";

try {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare($select . " WHERE o.idObjectif = :id");
        $stmt->execute(["id" => $_GET['id']]);
        $objectif = $stmt->fetch();

        if (!$objectif) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Objectif de formation introuvable."]);
            exit;
        }

        echo json_encode(["success" => true, "data" => $objectif]);
    } else {
        $stmt = $pdo->query($select . " ORDER BY o.periode DESC");
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la lecture : " . $e->getMessage()]);
}