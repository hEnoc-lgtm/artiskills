<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

// Fonction pour normaliser le nom (tirée de votre script)
function normaliser(string $texte): string {
    $texte = trim($texte);
    $texte = iconv('UTF-8', 'ASCII//TRANSLIT', $texte);
    return strtoupper($texte);
}

// Fonction intelligente : Vérifie si le quartier existe, sinon le crée et retourne l'ID
function getOrCreateQuartier($pdo, $nom_quartier, $id_arrondissement, $latitude, $longitude) {
    $nom_normalise = normaliser($nom_quartier);

    // 1. Vérifier s'il existe déjà
    $stmtCheck = $pdo->prepare("SELECT id_quartier FROM quartier_village WHERE nom_normalise = :nom AND id_arrondissement = :id_arr");
    $stmtCheck->execute(['nom' => $nom_normalise, 'id_arr' => $id_arrondissement]);
    $existant = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($existant) {
        return $existant['id_quartier']; // Il existe, on retourne son ID
    }

    // 2. S'il n'existe pas, on l'insère avec les coordonnées OSM
    // Note: Si l'API n'a pas trouvé les coords, elles seront NULL, ce qui est gérable
    $stmtInsert = $pdo->prepare("
        INSERT INTO quartier_village (nom_quartier, nom_normalise, id_arrondissement, latitude, longitude)
        VALUES (:nom, :nom_norm, :id_arr, :lat, :lon)
    ");
    $stmtInsert->execute([
        'nom' => $nom_quartier,
        'nom_norm' => $nom_normalise,
        'id_arr' => $id_arrondissement,
        'lat' => $latitude,
        'lon' => $longitude
    ]);

    return $pdo->lastInsertId(); // Retourne le nouvel ID créé
}

// Récupération des données
$data = json_decode(file_get_contents("php://input"), true);

$idArtisan = $data['idArtisan'] ?? null;
$code_corpsmetier = $data['code_corpsmetier'] ?? null;
$nbrAnExp = $data['nbrAnExp'] ?: null;

// Données Résidence
$id_arrond_res = $data['id_arrondissement_residence'] ?? null;
$nom_quartier_res = $data['nom_quartier_residence'] ?? null;
$lat_res = $data['latitude_residence'] ?: null;
$lon_res = $data['longitude_residence'] ?: null;

// Données Atelier
$id_arrond_ate = $data['id_arrondissement_atelier'] ?? null;
$nom_quartier_ate = $data['nom_quartier_atelier'] ?? null;
$lat_ate = $data['latitude_atelier'] ?: null;
$lon_ate = $data['longitude_atelier'] ?: null;

if (!$idArtisan || !$code_corpsmetier || !$id_arrond_res || !$nom_quartier_res || !$id_arrond_ate || !$nom_quartier_ate) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Données géographiques ou professionnelles incomplètes."]);
    exit;
}

try {
    $pdo->beginTransaction();

    // Résolution des IDs de quartiers (Check or Create)
    $id_quartier_residence = getOrCreateQuartier($pdo, $nom_quartier_res, $id_arrond_res, $lat_res, $lon_res);
    $id_quartier_atelier = getOrCreateQuartier($pdo, $nom_quartier_ate, $id_arrond_ate, $lat_ate, $lon_ate);

    // Mise à jour de la fiche de l'artisan
    $stmtUpdate = $pdo->prepare("
        UPDATE artisan 
        SET code_corpsmetier = :metier, 
            nbrAnExp = :exp, 
            id_quartier_residence = :qr, 
            id_quartier_atelier = :qa 
        WHERE id_artisan = :id
    ");
    
    $stmtUpdate->execute([
        'metier' => $code_corpsmetier,
        'exp' => $nbrAnExp,
        'qr' => $id_quartier_residence,
        'qa' => $id_quartier_atelier,
        'id' => $idArtisan
    ]);

    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Profil enregistré avec succès."]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur base de données : " . $e->getMessage()]);
}
?>