<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

/**
 * Normalise un nom de quartier pour la détection de doublons :
 * majuscules, sans accents, espaces superflus retirés.
 */
function normaliser(string $texte): string
{
    $texte = trim($texte);
    $texte = iconv('UTF-8', 'ASCII//TRANSLIT', $texte);
    return strtoupper($texte);
}

$donnees = json_decode(file_get_contents("php://input"), true);
$nom_quartier = trim($donnees['nom_quartier'] ?? '');
$id_arrondissement = $donnees['id_arrondissement'] ?? null;
$longitude = $donnees['longitude'] ?? null;
$latitude = $donnees['latitude'] ?? null;
// nom_normalise est calculé automatiquement à partir de nom_quartier si non fourni
$nom_normalise = trim($donnees['nom_normalise'] ?? '') ?: normaliser($nom_quartier);

if ($nom_quartier === '' || !$id_arrondissement || $longitude === null || $latitude === null) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Les champs 'nom_quartier', 'id_arrondissement', 'longitude' et 'latitude' sont obligatoires."]);
    exit;
}

try {
    $verif = $pdo->prepare("SELECT id_arrondissement FROM arrondissement WHERE id_arrondissement = :id");
    $verif->execute(["id" => $id_arrondissement]);
    if (!$verif->fetch()) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "L'arrondissement indiqué n'existe pas."]);
        exit;
    }

    $verifDoublon = $pdo->prepare("SELECT id_quartier FROM quartier_village WHERE nom_normalise = :nom_normalise AND id_arrondissement = :id_arrondissement");
    $verifDoublon->execute(["nom_normalise" => $nom_normalise, "id_arrondissement" => $id_arrondissement]);
    if ($verifDoublon->fetch()) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Ce quartier existe déjà dans cet arrondissement."]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO quartier_village (nom_quartier, nom_normalise, id_arrondissement, longitude, latitude)
        VALUES (:nom_quartier, :nom_normalise, :id_arrondissement, :longitude, :latitude)
    ");
    $stmt->execute([
        "nom_quartier" => $nom_quartier,
        "nom_normalise" => $nom_normalise,
        "id_arrondissement" => $id_arrondissement,
        "longitude" => $longitude,
        "latitude" => $latitude,
    ]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Quartier/village créé avec succès.",
        "data" => ["id_quartier" => (int) $pdo->lastInsertId(), "nom_quartier" => $nom_quartier],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création : " . $e->getMessage()]);
}

