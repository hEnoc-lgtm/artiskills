<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

// motDepasse volontairement exclu de toutes les réponses (donnée sensible hachée)
$colonnes = "id_profil, nom, prenom, contact, dateCreation, sexe, emailPro, service, dernierAcces, role";

try {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT $colonnes FROM profil WHERE id_profil = :id");
        $stmt->execute(["id" => $_GET['id']]);
        $profil = $stmt->fetch();

        if (!$profil) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Profil introuvable."]);
            exit;
        }

        echo json_encode(["success" => true, "data" => $profil]);
    } else {
        $stmt = $pdo->query("SELECT $colonnes FROM profil ORDER BY nom ASC, prenom ASC");
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la lecture : " . $e->getMessage()]);
}