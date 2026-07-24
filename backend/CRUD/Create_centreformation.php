<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée. Utilisez POST."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);

// Validation des colonnes exactes de votre table SQL
$champsObligatoires = ["nomCentre", "contactCentre", "id_quartier_centre"];
foreach ($champsObligatoires as $champ) {
    if (empty($donnees[$champ]) && $donnees[$champ] !== 0) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "Le champ obligatoire '$champ' est manquant."]);
        exit;
    }
}

try {
    // Vérification de la contrainte : le quartier du centre doit exister dans la BDD
    $verifQuartier = $pdo->prepare("SELECT id_quartier FROM quartier_village WHERE id_quartier = :id");
    $verifQuartier->execute(["id" => $donnees['id_quartier_centre']]);
    if (!$verifQuartier->fetch()) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "Le quartier spécifié pour le centre n'existe pas."]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO centre_formation (nomCentre, contactCentre, id_quartier_centre)
        VALUES (:nom, :contact, :idQuartier)
    ");
    
    $stmt->execute([
        "nom" => $donnees['nomCentre'],
        "contact" => $donnees['contactCentre'],
        "idQuartier" => (int)$donnees['id_quartier_centre']
    ]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Centre de formation ARCH enregistré avec succès.",
        "data" => ["idCentre" => (int)$pdo->lastInsertId()]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création : " . $e->getMessage()]);
}
