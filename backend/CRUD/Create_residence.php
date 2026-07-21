<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

// Note : en pratique, la résidence d'un artisan est déjà créée lors de
// l'inscription (voir api/artisan/inscription.php). Ce endpoint sert pour
// les cas particuliers (ré-import de données, correction manuelle, etc.)

$donnees = json_decode(file_get_contents("php://input"), true);
$id_arrondissement = $donnees['id_arrondissement'] ?? null;
$id_artisan = $donnees['id_artisan'] ?? null;
$complement = trim($donnees['complement'] ?? '');

if (!$id_arrondissement || !$id_artisan) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Les champs 'id_arrondissement' et 'id_artisan' sont obligatoires."]);
    exit;
}

try {
    $verifArtisan = $pdo->prepare("SELECT id_artisan FROM artisan WHERE id_artisan = :id_artisan");
    $verifArtisan->execute(["id_artisan" => $id_artisan]);
    if (!$verifArtisan->fetch()) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "L'artisan indiqué n'existe pas."]);
        exit;
    }

    $verifExiste = $pdo->prepare("SELECT idAdresse FROM residence WHERE id_artisan = :id_artisan");
    $verifExiste->execute(["id_artisan" => $id_artisan]);
    if ($verifExiste->fetch()) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Cet artisan a déjà une résidence enregistrée. Utilisez la modification."]);
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
        INSERT INTO residence (nom_commune, nom_departement, nom_arrondissement, complement, id_arrondissement, id_artisan)
        VALUES (:nom_commune, :nom_departement, :nom_arrondissement, :complement, :id_arrondissement, :id_artisan)
    ");
    $stmt->execute([
        "nom_commune" => $loc['nomCommune'],
        "nom_departement" => $loc['nomDepartement'],
        "nom_arrondissement" => $loc['nom_arrondissement'],
        "complement" => $complement !== '' ? $complement : null,
        "id_arrondissement" => $loc['id_arrondissement'],
        "id_artisan" => $id_artisan,
    ]);

    http_response_code(201);
    echo json_encode(["success" => true, "message" => "Résidence créée avec succès.", "data" => ["idAdresse" => (int) $pdo->lastInsertId()]]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création : " . $e->getMessage()]);
}