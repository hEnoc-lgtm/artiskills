<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$idCommune = $donnees['idCommune'] ?? null;
$nomCommune = trim($donnees['nomCommune'] ?? '');
$idDepart = $donnees['idDepart'] ?? null;

if (!$idCommune || $nomCommune === '' || !$idDepart) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Les champs 'idCommune', 'nomCommune' et 'idDepart' sont obligatoires."]);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE commune SET nomCommune = :nomCommune, idDepart = :idDepart WHERE idCommune = :idCommune");
    $stmt->execute(["nomCommune" => $nomCommune, "idDepart" => $idDepart, "idCommune" => $idCommune]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Commune introuvable ou aucune modification effectuée."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Commune mise à jour avec succès."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la mise à jour : " . $e->getMessage()]);
}