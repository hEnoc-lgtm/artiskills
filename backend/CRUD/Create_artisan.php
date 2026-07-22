<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);

$champsObligatoires = ["nom", "prenom", "contact", "sexe", "nbrAnExp", "codePin", "code_corpsmetier", "id_quartier_residence"];
foreach ($champsObligatoires as $champ) {
    if (empty($donnees[$champ]) && $donnees[$champ] !== 0) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "Le champ '$champ' est obligatoire."]);
        exit;
    }
}

if (!preg_match('/^[0-9]{4}$/', (string) $donnees['codePin'])) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Le code PIN doit contenir exactement 4 chiffres."]);
    exit;
}

try {
    // Numéro de téléphone déjà utilisé ?
    $verif = $pdo->prepare("SELECT id_artisan FROM artisan WHERE contact = :contact");
    $verif->execute(["contact" => $donnees['contact']]);
    if ($verif->fetch()) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Ce numéro de téléphone est déjà inscrit."]);
        exit;
    }

    // Le corps de métier existe-t-il ?
    $verifMetier = $pdo->prepare("SELECT code_corpsmetier FROM corps_metier WHERE code_corpsmetier = :code");
    $verifMetier->execute(["code" => $donnees['code_corpsmetier']]);
    if (!$verifMetier->fetch()) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "Le corps de métier indiqué n'existe pas."]);
        exit;
    }

    // Le quartier de résidence existe-t-il ?
    $verifQuartierRes = $pdo->prepare("SELECT id_quartier FROM quartier_village WHERE id_quartier = :id");
    $verifQuartierRes->execute(["id" => $donnees['id_quartier_residence']]);
    if (!$verifQuartierRes->fetch()) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "Le quartier de résidence indiqué n'existe pas."]);
        exit;
    }

    // Le quartier d'atelier, s'il est fourni, doit aussi exister
    $id_quartier_atelier = $donnees['id_quartier_atelier'] ?? null;
    if ($id_quartier_atelier) {
        $verifQuartierAtelier = $pdo->prepare("SELECT id_quartier FROM quartier_village WHERE id_quartier = :id");
        $verifQuartierAtelier->execute(["id" => $id_quartier_atelier]);
        if (!$verifQuartierAtelier->fetch()) {
            http_response_code(422);
            echo json_encode(["success" => false, "message" => "Le quartier d'atelier indiqué n'existe pas."]);
            exit;
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO artisan (nom, prenom, contact, sexe, nbrAnExp, codePin, code_corpsmetier, id_quartier_residence, id_quartier_atelier)
        VALUES (:nom, :prenom, :contact, :sexe, :nbrAnExp, :codePin, :code_corpsmetier, :id_quartier_residence, :id_quartier_atelier)
    ");
    $stmt->execute([
        "nom" => $donnees['nom'],
        "prenom" => $donnees['prenom'],
        "contact" => $donnees['contact'],
        "sexe" => $donnees['sexe'],
        "nbrAnExp" => $donnees['nbrAnExp'],
        "codePin" => password_hash((string) $donnees['codePin'], PASSWORD_DEFAULT), // haché avant stockage
        "code_corpsmetier" => $donnees['code_corpsmetier'],
        "id_quartier_residence" => $donnees['id_quartier_residence'],
        "id_quartier_atelier" => $id_quartier_atelier,
    ]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Artisan inscrit avec succès.",
        "data" => ["id_artisan" => (int) $pdo->lastInsertId()],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création : " . $e->getMessage()]);
}