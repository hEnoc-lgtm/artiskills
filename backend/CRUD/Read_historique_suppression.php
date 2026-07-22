<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$select = "
    SELECT h.idHistorique, h.enonceSup, h.reponsesSup, h.dateSuppression, h.heureSuppression,
           h.id_admin, p.nom, p.prenom
    FROM historique_suppression h JOIN profil p ON p.id_profil = h.id_admin
";

try {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare($select . " WHERE h.idHistorique = :id");
        $stmt->execute(["id" => $_GET['id']]);
        $historique = $stmt->fetch();

        if (!$historique) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Entrée d'historique introuvable."]);
            exit;
        }

        echo json_encode(["success" => true, "data" => $historique]);
    } elseif (isset($_GET['id_admin'])) {
        $stmt = $pdo->prepare($select . " WHERE h.id_admin = :id_admin ORDER BY h.dateSuppression DESC, h.heureSuppression DESC");
        $stmt->execute(["id_admin" => $_GET['id_admin']]);
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    } else {
        $stmt = $pdo->query($select . " ORDER BY h.dateSuppression DESC, h.heureSuppression DESC");
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la lecture : " . $e->getMessage()]);
}