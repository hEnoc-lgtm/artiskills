<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$idCentre = $donnees['idCentre'] ?? null;
$nomCentre = trim($donnees['nomCentre'] ?? '');
$contactCentre = trim($donnees['contactCentre'] ?? '');
$id_quartier_centre = $donnees['id_quartier_centre'] ?? null;

if (!$idCentre || $nomCentre === '' || !$id_quartier_centre) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Les champs 'idCentre', 'nomCentre' et 'id_quartier_centre' sont obligatoires."]);
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

    $stmt = $pdo->prepare("
        UPDATE centre_formation SET nomCentre = :nomCentre, contactCentre = :contactCentre, id_quartier_centre = :id_quartier_centre
        WHERE idCentre = :idCentre
    ");
    $stmt->execute([
        "nomCentre" => $nomCentre,
        "contactCentre" => $contactCentre !== '' ? $contactCentre : null,
        "id_quartier_centre" => $id_quartier_centre,
        "idCentre" => $idCentre,
    ]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Centre de formation introuvable ou aucune modification effectuée."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Centre de formation mis à jour avec succès."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la mise à jour : " . $e->getMessage()]);
}