<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$nomCentre = trim($donnees['nomCentre'] ?? '');
$contactCentre = trim($donnees['contactCentre'] ?? '');
$id_quartier_centre = $donnees['id_quartier_centre'] ?? null;

if ($nomCentre === '' || !$id_quartier_centre) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Les champs 'nomCentre' et 'id_quartier_centre' sont obligatoires."]);
    exit;
}

try {
    $verif = $pdo->prepare("SELECT id_quartier FROM quartier_village WHERE id_quartier = :id");
    $verif->execute(["id" => $id_quartier_centre]);
    if (!$verif->fetch()) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "Le quartier indiqué n'existe pas."]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO centre_formation (nomCentre, contactCentre, id_quartier_centre) VALUES (:nomCentre, :contactCentre, :id_quartier_centre)");
    $stmt->execute([
        "nomCentre" => $nomCentre,
        "contactCentre" => $contactCentre !== '' ? $contactCentre : null,
        "id_quartier_centre" => $id_quartier_centre,
    ]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Centre de formation créé avec succès.",
        "data" => ["idCentre" => (int) $pdo->lastInsertId(), "nomCentre" => $nomCentre],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création : " . $e->getMessage()]);
}