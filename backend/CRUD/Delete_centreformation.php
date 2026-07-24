<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$idCentre = $donnees['idCentre'] ?? null;

if (!$idCentre) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "L'identifiant 'idCentre' est requis."]);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM centre_formation WHERE idCentre = :id");
    $stmt->execute(["id" => $idCentre]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Centre supprimé du registre avec succès."]);
    } else {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Ce centre de formation n'existe pas."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la suppression : " . $e->getMessage()]);
}
