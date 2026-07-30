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
        // Lecture d'un profil unique (utile pour l'édition)
        $stmt = $pdo->prepare("
            SELECT id_profil, nom, prenom, contact, sexe, emailPro, service, role, dernierAcces 
            FROM profil 
            WHERE id_profil = :id
        ");
        $stmt->execute(["id" => $id]);
        $profil = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$profil) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Profil utilisateur introuvable."]);
            exit;
        }
        echo json_encode(["success" => true, "data" => $profil]);
    } else {
        // Liste globale de tous les profils pour le tableau du Dashboard
        $stmt = $pdo->query("
            SELECT id_profil, nom, prenom, contact, sexe, emailPro, service, role, dernierAcces 
            FROM profil 
            ORDER BY id_profil DESC
        ");
        $profils = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $profils]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur de lecture : " . $e->getMessage()]);
}
?>