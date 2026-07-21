<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$idDepart = $donnees['idDepart'] ?? null;
$nomDepartement = trim($donnees['nomDepartement'] ?? '');

if (!$idDepart || $nomDepartement === '') {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Les champs 'idDepart' et 'nomDepartement' sont obligatoires."]);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE departement SET nomDepartement = :nomDepartement WHERE idDepart = :idDepart");
    $stmt->execute(["nomDepartement" => $nomDepartement, "idDepart" => $idDepart]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Département introuvable ou aucune modification effectuée."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Département mis à jour avec succès."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la mise à jour : " . $e->getMessage()]);
}