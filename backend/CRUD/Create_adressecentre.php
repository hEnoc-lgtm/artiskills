<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$id_arrondissement = $donnees['id_arrondissement'] ?? null;
$idCentre = $donnees['idCentre'] ?? null;
$complement = trim($donnees['complement'] ?? '');

if (!$id_arrondissement || !$idCentre) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Les champs 'id_arrondissement' et 'idCentre' sont obligatoires."]);
    exit;
}

try {
    $verifCentre = $pdo->prepare("SELECT idCentre FROM centre_formation WHERE idCentre = :idCentre");
    $verifCentre->execute(["idCentre" => $idCentre]);
    if (!$verifCentre->fetch()) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "Le centre de formation indiqué n'existe pas."]);
        exit;
    }

    $verifExiste = $pdo->prepare("SELECT idAdresse FROM adresse_centre WHERE idCentre = :idCentre");
    $verifExiste->execute(["idCentre" => $idCentre]);
    if ($verifExiste->fetch()) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Ce centre a déjà une adresse enregistrée. Utilisez la modification."]);
        exit;
    }

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
        INSERT INTO adresse_centre (nom_commune, nom_departement, nom_arrondissement, complement, id_arrondissement, idCentre)
        VALUES (:nom_commune, :nom_departement, :nom_arrondissement, :complement, :id_arrondissement, :idCentre)
    ");
    $stmt->execute([
        "nom_commune" => $loc['nomCommune'],
        "nom_departement" => $loc['nomDepartement'],
        "nom_arrondissement" => $loc['nom_arrondissement'],
        "complement" => $complement !== '' ? $complement : null,
        "id_arrondissement" => $loc['id_arrondissement'],
        "idCentre" => $idCentre,
    ]);

    http_response_code(201);
    echo json_encode(["success" => true, "message" => "Adresse de centre créée avec succès.", "data" => ["idAdresse" => (int) $pdo->lastInsertId()]]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création : " . $e->getMessage()]);
}