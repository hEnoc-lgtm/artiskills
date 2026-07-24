<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);

// Validation stricte de vos 3 colonnes d'insertion
$champsObligatoires = ["nombrePlaces", "periode", "code_corpsmetier"];
foreach ($champsObligatoires as $champ) {
    if (!isset($donnees[$champ]) || trim($donnees[$champ]) === '') {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "Le champ '$champ' est obligatoire."]);
        exit;
    }
}

try {
    // Vérification de la clé étrangère corps_metier
    $verif = $pdo->prepare("SELECT code_corpsmetier FROM corps_metier WHERE code_corpsmetier = :code");
    $verif->execute(["code" => $donnees['code_corpsmetier']]);
    if (!$verif->fetch()) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "Le corps de métier indiqué n'existe pas."]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO objectif_formation (nombrePlaces, periode, code_corpsmetier) 
        VALUES (:places, :periode, :metier)
    ");
    $stmt->execute([
        "places" => (int)$donnees['nombrePlaces'],
        "periode" => trim($donnees['periode']),
        "metier" => trim($donnees['code_corpsmetier'])
    ]);

    http_response_code(201);
    echo json_encode(["success" => true, "message" => "Objectif de places allouées créé avec succès."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
