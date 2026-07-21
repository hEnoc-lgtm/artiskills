<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

/**
 * POST /api/artisan/inscription.php
 * Corps attendu (JSON) :
 * {
 *   "nom": "HOUNSOU",
 *   "prenom": "Koffi",
 *   "contact": "0196574823",
 *   "sexe": "Masculin",
 *   "nbrAnExp": 6,
 *   "codePin": "4821",
 *   "code_corpsmetier": "MEN",
 *   "residence": { "id_arrondissement": 34, "complement": "Quartier Zongo" },
 *   "atelier":   { "id_arrondissement": 34, "complement": "Zone artisanale" }  // optionnel
 * }
 */

// On n'accepte que les requêtes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

// Récupération et décodage du corps de la requête
$donnees = json_decode(file_get_contents("php://input"), true);

// --- Validation des champs obligatoires ---
$champsObligatoires = ["nom", "prenom", "contact", "sexe", "nbrAnExp", "codePin", "code_corpsmetier", "residence"];
foreach ($champsObligatoires as $champ) {
    if (empty($donnees[$champ]) && $donnees[$champ] !== 0) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "Le champ '$champ' est obligatoire."]);
        exit;
    }
}

if (!preg_match('/^[0-9]{4}$/', $donnees['codePin'])) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Le code PIN doit contenir exactement 4 chiffres."]);
    exit;
}

if (empty($donnees['residence']['id_arrondissement'])) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "L'arrondissement de résidence est obligatoire."]);
    exit;
}

try {
    // Vérifie si ce numéro de téléphone est déjà inscrit
    $verif = $pdo->prepare("SELECT id_artisan FROM artisan WHERE contact = :contact");
    $verif->execute(["contact" => $donnees['contact']]);
    if ($verif->fetch()) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Ce numéro de téléphone est déjà inscrit."]);
        exit;
    }

    $pdo->beginTransaction();

    // 1. Insertion de l'artisan
    $stmt = $pdo->prepare("
        INSERT INTO artisan (nom, prenom, contact, sexe, nbrAnExp, codePin, code_corpsmetier)
        VALUES (:nom, :prenom, :contact, :sexe, :nbrAnExp, :codePin, :code_corpsmetier)
    ");
    $stmt->execute([
        "nom"              => $donnees['nom'],
        "prenom"           => $donnees['prenom'],
        "contact"          => $donnees['contact'],
        "sexe"             => $donnees['sexe'],
        "nbrAnExp"         => $donnees['nbrAnExp'],
        "codePin"          => password_hash($donnees['codePin'], PASSWORD_DEFAULT),
        "code_corpsmetier" => $donnees['code_corpsmetier'],
    ]);
    $idArtisan = $pdo->lastInsertId();

    // Requête commune pour récupérer les noms (arrondissement/commune/département)
    // à partir d'un id_arrondissement
    $sqlLocalisation = "
        SELECT a.nom_arrondissement, c.nomCommune, d.nomDepartement, a.id_arrondissement
        FROM arrondissement a
        JOIN commune c ON c.idCommune = a.idCommune
        JOIN departement d ON d.idDepart = c.idDepart
        WHERE a.id_arrondissement = :id_arrondissement
    ";

    // 2. Insertion de la résidence (obligatoire)
    $stmtLocRes = $pdo->prepare($sqlLocalisation);
    $stmtLocRes->execute(["id_arrondissement" => $donnees['residence']['id_arrondissement']]);
    $locRes = $stmtLocRes->fetch();

    if (!$locRes) {
        throw new Exception("Arrondissement de résidence introuvable.");
    }

    $stmtRes = $pdo->prepare("
        INSERT INTO residence (nom_commune, nom_departement, nom_arrondissement, complement, id_arrondissement, id_artisan)
        VALUES (:nom_commune, :nom_departement, :nom_arrondissement, :complement, :id_arrondissement, :id_artisan)
    ");
    $stmtRes->execute([
        "nom_commune"       => $locRes['nomCommune'],
        "nom_departement"   => $locRes['nomDepartement'],
        "nom_arrondissement" => $locRes['nom_arrondissement'],
        "complement"        => $donnees['residence']['complement'] ?? null,
        "id_arrondissement" => $locRes['id_arrondissement'],
        "id_artisan"        => $idArtisan,
    ]);

    // 3. Insertion de l'atelier (optionnel)
    if (!empty($donnees['atelier']['id_arrondissement'])) {
        $stmtLocAtelier = $pdo->prepare($sqlLocalisation);
        $stmtLocAtelier->execute(["id_arrondissement" => $donnees['atelier']['id_arrondissement']]);
        $locAtelier = $stmtLocAtelier->fetch();

        if (!$locAtelier) {
            throw new Exception("Arrondissement de l'atelier introuvable.");
        }

        $stmtAtelier = $pdo->prepare("
            INSERT INTO adresse_atelier (nom_commune, nom_departement, nom_arrondissement, complement, id_arrondissement, id_artisan)
            VALUES (:nom_commune, :nom_departement, :nom_arrondissement, :complement, :id_arrondissement, :id_artisan)
        ");
        $stmtAtelier->execute([
            "nom_commune"        => $locAtelier['nomCommune'],
            "nom_departement"    => $locAtelier['nomDepartement'],
            "nom_arrondissement" => $locAtelier['nom_arrondissement'],
            "complement"         => $donnees['atelier']['complement'] ?? null,
            "id_arrondissement"  => $locAtelier['id_arrondissement'],
            "id_artisan"         => $idArtisan,
        ]);
    }

    $pdo->commit();

    http_response_code(201);
    echo json_encode([
        "success"    => true,
        "message"    => "Inscription réussie.",
        "id_artisan" => $idArtisan,
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de l'inscription : " . $e->getMessage()]);
}