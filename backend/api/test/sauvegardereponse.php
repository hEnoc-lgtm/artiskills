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
$idTest = $donnees['idTest'] ?? null;
$idQuestion = $donnees['idQuestion'] ?? null;
$idReponse = $donnees['idReponse'] ?? null;         // Identifiant numérique de la réponse choisie
$libelleReponse = $donnees['libelleReponse'] ?? null; // Libellé textuel de la réponse pour la colonne reponseDonnee

// Validation des données obligatoires pour respecter l'intégrité de la table
if (!$idTest || !$idQuestion || !$idReponse || !$libelleReponse) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Données incomplètes pour le traitement de la réponse."]);
    exit;
}

try {
    // 1. VÉRIFICATION : On vérifie si la réponse choisie par l'artisan est correcte
    // Le nom de la colonne dépend de votre table 'reponse' (ex: estCorrecte, statut, ou valeur)
    // Nous récupérons un booléen/tinyint (1 si vrai, 0 si faux)
    $stmtCheck = $pdo->prepare("SELECT estCorrecte FROM reponse WHERE idReponse = :idReponse");
    $stmtCheck->execute(['idReponse' => $idReponse]);
    $isCorrect = (int) $stmtCheck->fetchColumn(); 

    // 2. MISE À JOUR : Enregistrement et verrouillage direct dans la table question_test
    // La condition 'estVerouillee = 0' empêche un artisan de re-soumettre une réponse déjà validée
    $stmtUpdate = $pdo->prepare("
        UPDATE question_test 
        SET reponseDonnee = :reponseDonnee,
            estVerouillee = 1,
            estcorrecte = :estcorrecte
        WHERE idTest = :idTest 
        AND idQuestion = :idQuestion 
        AND estVerouillee = 0
    ");
    
    $stmtUpdate->execute([
        'reponseDonnee' => $libelleReponse, // Remplit votre colonne varchar(255)
        'estcorrecte'   => $isCorrect,      // Remplit votre colonne tinyint(1)
        'idTest'        => $idTest,
        'idQuestion'    => $idQuestion
    ]);

    // On vérifie si une ligne a bien été modifiée par la requête
    if ($stmtUpdate->rowCount() > 0) {
        echo json_encode([
            "success" => true, 
            "message" => "Réponse enregistrée et validée définitivement en base de données."
        ]);
    } else {
        // Cas où la question était déjà verrouillée en amont pour éviter les tentatives de fraude
        http_response_code(409);
        echo json_encode([
            "success" => false, 
            "message" => "Cette question a déjà fait l'objet d'une validation verrouillée ou n'existe pas."
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "message" => "Erreur critique de mise à jour de la table question_test : " . $e->getMessage()
    ]);
}
