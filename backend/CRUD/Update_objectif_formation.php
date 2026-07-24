<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$idObjectif = $donnees['idObjectif'] ?? null;

if (!$idObjectif) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "L'identifiant 'idObjectif' est requis."]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE objectif_formation 
        SET nombrePlaces = :places, periode = :periode, code_corpsmetier = :metier
        WHERE idObjectif = :id
    ");
    $stmt->execute([
        "places" => (int)$donnees['nombrePlaces'],
        "periode" => trim($donnees['periode']),
        "metier" => trim($donnees['code_corpsmetier']),
        "id" => $idObjectif
    ]);

    echo json_encode(["success" => true, "message" => "Objectif de formation mis à jour."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur SQL : " . $e->getMessage()]);
}
