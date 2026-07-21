<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$nomCommune = trim($donnees['nomCommune'] ?? '');
$idDepart = $donnees['idDepart'] ?? null;

if ($nomCommune === '' || !$idDepart) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Les champs 'nomCommune' et 'idDepart' sont obligatoires."]);
    exit;
}

try {
    $verif = $pdo->prepare("SELECT idDepart FROM departement WHERE idDepart = :idDepart");
    $verif->execute(["idDepart" => $idDepart]);
    if (!$verif->fetch()) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "Le département indiqué n'existe pas."]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO commune (nomCommune, idDepart) VALUES (:nomCommune, :idDepart)");
    $stmt->execute(["nomCommune" => $nomCommune, "idDepart" => $idDepart]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Commune créée avec succès.",
        "data" => ["idCommune" => (int) $pdo->lastInsertId(), "nomCommune" => $nomCommune, "idDepart" => (int) $idDepart],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création : " . $e->getMessage()]);
}