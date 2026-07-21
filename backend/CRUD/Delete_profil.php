<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$id_profil = $donnees['id_profil'] ?? $_GET['id'] ?? null;

if (!$id_profil) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Le champ 'id_profil' est obligatoire."]);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM profil WHERE id_profil = :id_profil");
    $stmt->execute(["id_profil" => $id_profil]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Profil introuvable."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Profil supprimé avec succès."]);
} catch (PDOException $e) {
    // Erreur typique : ce profil a encore des suppressions tracées dans historique_suppression (ON DELETE RESTRICT)
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Suppression impossible : " . $e->getMessage()]);
}