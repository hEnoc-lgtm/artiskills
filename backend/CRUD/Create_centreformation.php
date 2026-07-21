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

if ($nomCentre === '') {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Le champ 'nomCentre' est obligatoire."]);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO centre_formation (nomCentre, contactCentre) VALUES (:nomCentre, :contactCentre)");
    $stmt->execute(["nomCentre" => $nomCentre, "contactCentre" => $contactCentre !== '' ? $contactCentre : null]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Centre de formation créé avec succès.",
        "data" => ["idCentre" => (int) $pdo->lastInsertId(), "nomCentre" => $nomCentre, "contactCentre" => $contactCentre],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création : " . $e->getMessage()]);
}