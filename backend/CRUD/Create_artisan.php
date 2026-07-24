<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);

$champsObligatoires = ["npi", "nom", "prenom", "contact", "sexe", "nbrAnExp", "code_corpsmetier", "id_quartier_residence"];
foreach ($champsObligatoires as $champ) {
    if (empty($donnees[$champ]) && $donnees[$champ] !== 0) {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "Le champ '$champ' est obligatoire."]);
        exit;
    }
}

try {
    // Unicité du NPI biométrique national
    $verifNpi = $pdo->prepare("SELECT id_artisan FROM artisan WHERE npi = :npi");
    $verifNpi->execute(["npi" => $donnees['npi']]);
    if ($verifNpi->fetch()) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Ce numéro NPI est déjà enregistré."]);
        exit;
    }

    $id_quartier_atelier = $donnees['id_quartier_atelier'] ?? null;

    $stmt = $pdo->prepare("
        INSERT INTO artisan (npi, nom, prenom, contact, sexe, nbrAnExp, code_corpsmetier, id_quartier_residence, id_quartier_atelier)
        VALUES (:npi, :nom, :prenom, :contact, :sexe, :nbrAnExp, :code_corpsmetier, :id_quartier_residence, :id_quartier_atelier)
    ");
    
    $stmt->execute([
        "npi" => $donnees['npi'],
        "nom" => $donnees['nom'],
        "prenom" => $donnees['prenom'],
        "contact" => $donnees['contact'],
        "sexe" => $donnees['sexe'],
        "nbrAnExp" => $donnees['nbrAnExp'],
        "code_corpsmetier" => $donnees['code_corpsmetier'],
        "id_quartier_residence" => $donnees['id_quartier_residence'],
        "id_quartier_atelier" => $id_quartier_atelier
    ]);

    // Tracé automatique dans la table historique avec l'option ENUM
    $stmtHist = $pdo->prepare("INSERT INTO historique_inscription (npi_artisan, action_effectuee) VALUES (:npi, 'Première inscription')");
    $stmtHist->execute(['npi' => $donnees['npi']]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Artisan créé avec succès dans le registre CRUD.",
        "data" => ["id_artisan" => (int)$pdo->lastInsertId()]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création : " . $e->getMessage()]);
}
