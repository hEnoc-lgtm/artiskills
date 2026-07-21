<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$id_artisan = $donnees['id_artisan'] ?? null;
$nom = trim($donnees['nom'] ?? '');
$prenom = trim($donnees['prenom'] ?? '');
$contact = trim($donnees['contact'] ?? '');
$sexe = $donnees['sexe'] ?? '';
$nbrAnExp = $donnees['nbrAnExp'] ?? null;
$code_corpsmetier = $donnees['code_corpsmetier'] ?? null;
// codePin est optionnel : on ne le change que si un nouveau code est fourni
$codePin = $donnees['codePin'] ?? null;

if (!$id_artisan || $nom === '' || $prenom === '' || $contact === '' || !$sexe || $nbrAnExp === null || !$code_corpsmetier) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Les champs nom, prenom, contact, sexe, nbrAnExp et code_corpsmetier sont obligatoires."]);
    exit;
}

if ($codePin !== null && !preg_match('/^[0-9]{4}$/', $codePin)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Le code PIN doit contenir exactement 4 chiffres."]);
    exit;
}

try {
    if ($codePin !== null) {
        $stmt = $pdo->prepare("
            UPDATE artisan
            SET nom = :nom, prenom = :prenom, contact = :contact, sexe = :sexe,
                nbrAnExp = :nbrAnExp, code_corpsmetier = :code_corpsmetier, codePin = :codePin
            WHERE id_artisan = :id_artisan
        ");
        $stmt->execute([
            "nom" => $nom, "prenom" => $prenom, "contact" => $contact, "sexe" => $sexe,
            "nbrAnExp" => $nbrAnExp, "code_corpsmetier" => $code_corpsmetier,
            "codePin" => password_hash($codePin, PASSWORD_DEFAULT), "id_artisan" => $id_artisan,
        ]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE artisan
            SET nom = :nom, prenom = :prenom, contact = :contact, sexe = :sexe,
                nbrAnExp = :nbrAnExp, code_corpsmetier = :code_corpsmetier
            WHERE id_artisan = :id_artisan
        ");
        $stmt->execute([
            "nom" => $nom, "prenom" => $prenom, "contact" => $contact, "sexe" => $sexe,
            "nbrAnExp" => $nbrAnExp, "code_corpsmetier" => $code_corpsmetier, "id_artisan" => $id_artisan,
        ]);
    }

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Artisan introuvable ou aucune modification effectuée."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Artisan mis à jour avec succès."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la mise à jour : " . $e->getMessage()]);
}