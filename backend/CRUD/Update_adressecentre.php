<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$idAdresse = $donnees['idAdresse'] ?? null;
$id_arrondissement = $donnees['id_arrondissement'] ?? null;
$complement = trim($donnees['complement'] ?? '');

if (!$idAdresse || !$id_arrondissement) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Les champs 'idAdresse' et 'id_arrondissement' sont obligatoires."]);
    exit;
}

try {
    $stmtLoc = $pdo->prepare("
        SELECT a.nom_arrondissement, c.nomCommune, d.nomDepartement, a.id_arrondissement
        FROM arrondissement a
        JOIN commune c ON c.idCommune = a.idCommune
        JOIN departement d ON d.idDepart = c.idDepart
        WHERE a.id_arrondissement = :id_arrondissement
    ");
    $stmtLoc->execute(["id_arrondissement" => $id_arrondissement]);
    $loc = $stmtLoc->fetch();

    if (!$loc) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "L'arrondissement indiqué n'existe pas."]);
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE adresse_centre
        SET nom_commune = :nom_commune, nom_departement = :nom_departement, nom_arrondissement = :nom_arrondissement,
            complement = :complement, id_arrondissement = :id_arrondissement
        WHERE idAdresse = :idAdresse
    ");
    $stmt->execute([
        "nom_commune" => $loc['nomCommune'],
        "nom_departement" => $loc['nomDepartement'],
        "nom_arrondissement" => $loc['nom_arrondissement'],
        "complement" => $complement !== '' ? $complement : null,
        "id_arrondissement" => $loc['id_arrondissement'],
        "idAdresse" => $idAdresse,
    ]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Adresse de centre introuvable ou aucune modification effectuée."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Adresse de centre mise à jour avec succès."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la mise à jour : " . $e->getMessage()]);
}