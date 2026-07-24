<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$idQuestion = $donnees['idQuestion'] ?? null;
$idAdminConnecte = $donnees['id_admin'] ?? 1; // ID de l'agent admin effectuant l'action (par défaut 1 pour les tests)

if (!$idQuestion) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "L'identifiant 'idQuestion' est obligatoire."]);
    exit;
}

try {
    $pdo->beginTransaction();

    // A. RÉCUPÉRATION DU CONTENU AVANT DESTRUCTION (Pour la boîte noire)
    $stmtQ = $pdo->prepare("SELECT enonce FROM question WHERE idQuestion = :id");
    $stmtQ->execute(['id' => $idQuestion]);
    $enonceSup = $stmtQ->fetchColumn();

    if (!$enonceSup) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Cette question n'existe pas ou a déjà été supprimée."]);
        exit;
    }

    // Récupérer toutes les réponses associées sous forme de chaîne de caractères
    $stmtR = $pdo->prepare("SELECT libelleReponse, estCorrecte FROM reponse WHERE idQuestion = :id");
    $stmtR->execute(['id' => $idQuestion]);
    $listeReponses = $stmtR->fetchAll(PDO::FETCH_ASSOC);
    
    $reponsesSupArray = [];
    foreach ($listeReponses as $rep) {
        $reponsesSupArray[] = ($rep['estCorrecte'] == 1 ? "[VRAI] " : "[FAUX] ") . $rep['libelleReponse'];
    }
    $reponsesSupTexte = implode(" | ", $reponsesSupArray); // Exemple: "[VRAI] Teck | [FAUX] Iroko"

    // B. ARCHIVAGE DANS L'HISTORIQUE DE SUPPRESSION DE LA BDD REAL
    $stmtLog = $pdo->prepare("
        INSERT INTO historique_suppression (enonceSup, reponsesSup, dateSuppression, heureSuppression, id_admin)
        VALUES (:enonce, :reponses, CURDATE(), CURTIME(), :admin)
    ");
    $stmtLog->execute([
        'enonce' => $enonceSup,
        'reponses' => $reponsesSupTexte,
        'admin' => $idAdminConnecte
    ]);

    // C. SUPPRESSION FINALE (La contrainte ON DELETE CASCADE de votre BDD nettoiera la table 'reponse' automatiquement)
    $stmtDelete = $pdo->prepare("DELETE FROM question WHERE idQuestion = :id");
    $stmtDelete->execute(['id' => $idQuestion]);

    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Question archivée dans l'historique et supprimée avec succès."]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la suppression sécurisée : " . $e->getMessage()]);
}
