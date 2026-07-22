<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$distanceCalculee = $donnees['distanceCalculee'] ?? null;
$adresseReference = trim($donnees['adresseReference'] ?? '');
$statutPlace = $donnees['statutPlace'] ?? '';
$idTest = $donnees['idTest'] ?? null;
$idCentre = $donnees['idCentre'] ?? null;

if ($distanceCalculee === null || $statutPlace === '' || !$idTest || !$idCentre) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Les champs 'distanceCalculee', 'statutPlace', 'idTest' et 'idCentre' sont obligatoires."]);
    exit;
}

// Valeurs exactes de l'ENUM en base
if (!in_array($statutPlace, ['validée directement', 'liste_attente'], true)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "statutPlace doit être 'validée directement' ou 'liste_attente'."]);
    exit;
}

try {
    $verifTest = $pdo->prepare("SELECT idTest FROM test WHERE idTest = :id");
    $verifTest->execute(["id" => $idTest]);
    if (!$verifTest->fetch()) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "Le test indiqué n'existe pas."]);
        exit;
    }

    $verifCentre = $pdo->prepare("SELECT idCentre FROM centre_formation WHERE idCentre = :id");
    $verifCentre->execute(["id" => $idCentre]);
    if (!$verifCentre->fetch()) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "Le centre de formation indiqué n'existe pas."]);
        exit;
    }

    // idTest est UNIQUE : un test ne peut avoir qu'une seule affectation
    $verifExiste = $pdo->prepare("SELECT idAffect FROM affectation WHERE idTest = :idTest");
    $verifExiste->execute(["idTest" => $idTest]);
    if ($verifExiste->fetch()) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Ce test a déjà une affectation enregistrée."]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO affectation (distanceCalculee, adresseReference, statutPlace, idTest, idCentre)
        VALUES (:distanceCalculee, :adresseReference, :statutPlace, :idTest, :idCentre)
    ");
    $stmt->execute([
        "distanceCalculee" => $distanceCalculee,
        "adresseReference" => $adresseReference !== '' ? $adresseReference : null,
        "statutPlace" => $statutPlace,
        "idTest" => $idTest,
        "idCentre" => $idCentre,
    ]);

    http_response_code(201);
    echo json_encode(["success" => true, "message" => "Affectation créée avec succès.", "data" => ["idAffect" => (int) $pdo->lastInsertId()]]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création : " . $e->getMessage()]);
}