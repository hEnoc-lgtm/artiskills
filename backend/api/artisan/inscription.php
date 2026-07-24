<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$npi = $donnees['npi'] ?? null;
$nom = $donnees['nom'] ?? null;
$prenom = $donnees['prenom'] ?? null;
$contact = $donnees['contact'] ?? null;
$sexe = $donnees['sexe'] ?? null;

// Validation des 5 champs obligatoires de l'étape 1
if (!$npi || !$nom || !$prenom || !$contact || !$sexe) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Le NPI, le nom, le prénom, le contact et le sexe sont obligatoires."]);
    exit;
}

try {
    // ÉTAPE 1 : Vérifier si ce NPI existe déjà
    $stmtCheck = $pdo->prepare("SELECT id_artisan FROM artisan WHERE npi = :npi");
    $stmtCheck->execute(['npi' => $npi]);
    $artisanExistant = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($artisanExistant) {
        $idArtisan = $artisanExistant['id_artisan'];

        $stmtTest = $pdo->prepare("SELECT idTest, heureDebut FROM test WHERE id_artisan = :id");
        $stmtTest->execute(['id' => $idArtisan]);
        $testExistant = $stmtTest->fetch(PDO::FETCH_ASSOC);

        if ($testExistant && $testExistant['heureDebut'] !== null) {
            $stmtHist = $pdo->prepare("INSERT INTO historique_inscription (npi_artisan, action_effectuee) VALUES (:npi, 'Réinscription détectée - Test débuté')");
            $stmtHist->execute(['npi' => $npi]);

            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Ce NPI a déjà été utilisé pour démarrer ou soumettre une évaluation."]);
            exit;
        }

        $stmtHist = $pdo->prepare("INSERT INTO historique_inscription (npi_artisan, action_effectuee) VALUES (:npi, 'Réinscription détectée - Test non débuté')");
        $stmtHist->execute(['npi' => $npi]);

        if (!$testExistant) {
            $stmtNewTest = $pdo->prepare("INSERT INTO test (id_artisan) VALUES (:id)");
            $stmtNewTest->execute(['id' => $idArtisan]);
            $idTest = $pdo->lastInsertId();
        } else {
            $idTest = $testExistant['idTest'];
        }

        echo json_encode([
            "success" => true,
            "dejaInscrit" => true,
            "message" => "Rappel : Vous vous étiez déjà inscrit sur la plateforme ArtiSkills.",
            "idArtisan" => (int)$idArtisan,
            "idTest" => (int)$idTest
        ]);
        exit;
    }

    // ÉTAPE 2 : Nouvel Artisan - Insertion des 5 champs de l'étape 1 uniquement
    $stmtInsert = $pdo->prepare("INSERT INTO artisan (npi, nom, prenom, contact, sexe) VALUES (:npi, :nom, :prenom, :contact, :sexe)");
    $stmtInsert->execute([
        'npi' => $npi, 
        'nom' => $nom, 
        'prenom' => $prenom,
        'contact' => $contact,
        'sexe' => $sexe
    ]);
    $newIdArtisan = $pdo->lastInsertId();

    $stmtHistNew = $pdo->prepare("INSERT INTO historique_inscription (npi_artisan, action_effectuee) VALUES (:npi, 'Première inscription')");
    $stmtHistNew->execute(['npi' => $npi]);

    $stmtTestNew = $pdo->prepare("INSERT INTO test (id_artisan) VALUES (:id)");
    $stmtTestNew->execute(['id' => $newIdArtisan]);
    $idTestCreated = $pdo->lastInsertId();

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "dejaInscrit" => false,
        "message" => "Inscription initiale validée avec succès.",
        "idArtisan" => (int)$newIdArtisan,
        "idTest" => (int)$idTestCreated
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur critique du serveur : " . $e->getMessage()]);
}
?>