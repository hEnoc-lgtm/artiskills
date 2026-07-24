<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$npi = $donnees['npi'] ?? null;

if (!$npi) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "NPI manquant."]);
    exit;
}

try {
    // On ajoute simplement une ligne dans l'historique pour dire qu'il a refusé de composer pour le moment
    $stmtHist = $pdo->prepare("
        INSERT INTO historique_inscription (npi_artisan, action_effectuee) 
        VALUES (:npi, 'Réinscription détectée - Test non débuté')
    ");
    $stmtHist->execute(['npi' => $npi]);

    echo json_encode(["success" => true, "message" => "Abandon tracé. Retour à l'accueil autorisé."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
