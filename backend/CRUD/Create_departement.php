<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$nomDepartement = trim($donnees['nomDepartement'] ?? '');

if ($nomDepartement === '') {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Le champ 'nomDepartement' est obligatoire."]);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO departement (nomDepartement) VALUES (:nomDepartement)");
    $stmt->execute(["nomDepartement" => $nomDepartement]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Département créé avec succès.",
        "data" => ["idDepart" => (int) $pdo->lastInsertId(), "nomDepartement" => $nomDepartement],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création : " . $e->getMessage()]);
}