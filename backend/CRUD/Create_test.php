<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$id_artisan = $donnees['id_artisan'] ?? null;
$heureDebut = trim($donnees['heureDebut'] ?? '');

if (!$id_artisan || $heureDebut === '') {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Les champs 'id_artisan' et 'heureDebut' sont obligatoires."]);
    exit;
}

try {
    $verifArtisan = $pdo->prepare("SELECT id_artisan FROM artisan WHERE id_artisan = :id");
    $verifArtisan->execute(["id" => $id_artisan]);
    if (!$verifArtisan->fetch()) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "L'artisan indiqué n'existe pas."]);
        exit;
    }

    // id_artisan est UNIQUE : un artisan ne peut avoir qu'un seul test
    $verifExiste = $pdo->prepare("SELECT idTest FROM test WHERE id_artisan = :id");
    $verifExiste->execute(["id" => $id_artisan]);
    if ($verifExiste->fetch()) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Cet artisan a déjà un test enregistré."]);
        exit;
    }

    // date, statutTest ('en_cours') sont gérés par défaut en base
    $stmt = $pdo->prepare("INSERT INTO test (heureDebut, id_artisan) VALUES (:heureDebut, :id_artisan)");
    $stmt->execute(["heureDebut" => $heureDebut, "id_artisan" => $id_artisan]);

    http_response_code(201);
    echo json_encode(["success" => true, "message" => "Test créé avec succès.", "data" => ["idTest" => (int) $pdo->lastInsertId()]]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création : " . $e->getMessage()]);
}