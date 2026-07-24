<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

// Sécurité : Bloquer si la méthode HTTP n'est pas un POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

// Récupération de la charge utile JSON envoyée par le composant React
$donnees = json_decode(file_get_contents("php://input"), true);
$npi = $donnees['npi'] ?? null;
$nom = $donnees['nom'] ?? null;
$prenom = $donnees['prenom'] ?? null;

// Validation des données obligatoires
if (!$npi || !$nom || !$prenom) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Le NPI, le nom et le prénom sont obligatoires pour l'identification."]);
    exit;
}

try {
    // ÉTAPE 1 : Vérifier si ce NPI existe déjà dans la base de données
    $stmtCheck = $pdo->prepare("SELECT id_artisan FROM artisan WHERE npi = :npi");
    $stmtCheck->execute(['npi' => $npi]);
    $artisanExistant = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($artisanExistant) {
        $idArtisan = $artisanExistant['id_artisan'];

        // Vérifier le statut du test associé pour cet artisan
        $stmtTest = $pdo->prepare("SELECT idTest, heureDebut FROM test WHERE id_artisan = :id");
        $stmtTest->execute(['id' => $idArtisan]);
        $testExistant = $stmtTest->fetch(PDO::FETCH_ASSOC);

        // CAS DE FRAUDE : Si le test a déjà commencé (heureDebut n'est plus NULL)
        if ($testExistant && $testExistant['heureDebut'] !== null) {
            
            // Enregistrement de la tentative suspecte dans l'ENUM de l'historique
            $stmtHist = $pdo->prepare("INSERT INTO historique_inscription (npi_artisan, action_effectuee) VALUES (:npi, 'Réinscription détectée - Test débuté')");
            $stmtHist->execute(['npi' => $npi]);

            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Ce NPI a déjà été utilisé pour démarrer ou soumettre une évaluation. Tentative unique autorisée."]);
            exit;
        }

        // CAS CONFORME : Déjà inscrit mais n'a pas fait le test lors de la session précédente
        // Enregistrement de l'action conforme aux options strictes de l'ENUM
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

    // ÉTAPE 2 : Premier enregistrement en base de données (Nouvel Artisan complet)
    $stmtInsert = $pdo->prepare("INSERT INTO artisan (npi, nom, prenom) VALUES (:npi, :nom, :prenom)");
    $stmtInsert->execute(['npi' => $npi, 'nom' => $nom, 'prenom' => $prenom]);
    $newIdArtisan = $pdo->lastInsertId();

    // Enregistrement de sa première inscription dans l'ENUM de l'historique
    $stmtHistNew = $pdo->prepare("INSERT INTO historique_inscription (npi_artisan, action_effectuee) VALUES (:npi, 'Première inscription')");
    $stmtHistNew->execute(['npi' => $npi]);

    // Création de sa ligne de test vide prête à être associée plus tard aux questions
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