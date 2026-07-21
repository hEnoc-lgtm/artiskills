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
            SELECT a.id_artisan, a.nom, a.prenom, a.contact, a.dateCreation, a.sexe, a.nbrAnExp,
                   a.code_corpsmetier, cm.libelle AS libelleMetier
            FROM artisan a
            JOIN corps_metier cm ON cm.code = a.code_corpsmetier
            WHERE a.id_artisan = :id
        ");
        $stmt->execute(["id" => $_GET['id']]);
        $artisan = $stmt->fetch();

        if (!$artisan) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Artisan introuvable."]);
            exit;
        }

        echo json_encode(["success" => true, "data" => $artisan]);
    } else {
        // codePin volontairement exclu des listings (donnée sensible hachée)
        $stmt = $pdo->query("
            SELECT a.id_artisan, a.nom, a.prenom, a.contact, a.dateCreation, a.sexe, a.nbrAnExp,
                   a.code_corpsmetier, cm.libelle AS libelleMetier
            FROM artisan a
            JOIN corps_metier cm ON cm.code = a.code_corpsmetier
            ORDER BY a.nom ASC, a.prenom ASC
        ");
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la lecture : " . $e->getMessage()]);
}