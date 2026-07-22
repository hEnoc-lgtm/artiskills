<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$enonceSup = trim($donnees['enonceSup'] ?? '');
$reponsesSup = trim($donnees['reponsesSup'] ?? '');
$heureSuppression = trim($donnees['heureSuppression'] ?? '');
$id_admin = $donnees['id_admin'] ?? null;

if ($enonceSup === '' || $reponsesSup === '' || $heureSuppression === '' || !$id_admin) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Les champs 'enonceSup', 'reponsesSup', 'heureSuppression' et 'id_admin' sont obligatoires."]);
    exit;
}

try {
    $verif = $pdo->prepare("SELECT id_profil FROM profil WHERE id_profil = :id");
    $verif->execute(["id" => $id_admin]);
    if (!$verif->fetch()) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "Le profil administrateur indiqué n'existe pas."]);
        exit;
    }

    // dateSuppression est gérée par défaut en base (curdate())
    $stmt = $pdo->prepare("
        INSERT INTO historique_suppression (enonceSup, reponsesSup, heureSuppression, id_admin)
        VALUES (:enonceSup, :reponsesSup, :heureSuppression, :id_admin)
    ");
    $stmt->execute([
        "enonceSup" => $enonceSup,
        "reponsesSup" => $reponsesSup,
        "heureSuppression" => $heureSuppression,
        "id_admin" => $id_admin,
    ]);

    http_response_code(201);
    echo json_encode(["success" => true, "message" => "Suppression tracée avec succès.", "data" => ["idHistorique" => (int) $pdo->lastInsertId()]]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création : " . $e->getMessage()]);
}