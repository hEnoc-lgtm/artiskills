<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$nom = trim($donnees['nom'] ?? '');
$prenom = trim($donnees['prenom'] ?? '');
$contact = trim($donnees['contact'] ?? '');
$sexe = $donnees['sexe'] ?? '';
$motdepasse = $donnees['motdepasse'] ?? '';
$emailPro = trim($donnees['emailPro'] ?? '');
$service = trim($donnees['service'] ?? '');
$role = $donnees['role'] ?? '';

if ($nom === '' || $prenom === '' || $contact === '' || $sexe === '' || $motdepasse === '' || $emailPro === '' || $service === '' || $role === '') {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Tous les champs sont obligatoires."]);
    exit;
}

if (!in_array($sexe, ['Masculin', 'Féminin'], true)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "sexe doit être 'Masculin' ou 'Féminin'."]);
    exit;
}

// Valeurs exactes de l'ENUM en base (attention à l'orthographe : 'administratueur')
if (!in_array($role, ['agent simple', 'administratueur'], true)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "role doit être 'agent simple' ou 'administratueur'."]);
    exit;
}

try {
    $verif = $pdo->prepare("SELECT id_profil FROM profil WHERE emailPro = :emailPro");
    $verif->execute(["emailPro" => $emailPro]);
    if ($verif->fetch()) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Cette adresse e-mail professionnelle est déjà utilisée."]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO profil (nom, prenom, contact, sexe, motDepasse, emailPro, service, role)
        VALUES (:nom, :prenom, :contact, :sexe, :motDepasse, :emailPro, :service, :role)
    ");
    $stmt->execute([
        "nom" => $nom,
        "prenom" => $prenom,
        "contact" => $contact,
        "sexe" => $sexe,
        "motDepasse" => password_hash($motdepasse, PASSWORD_DEFAULT),
        "emailPro" => $emailPro,
        "service" => $service,
        "role" => $role,
    ]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Profil créé avec succès.",
        "data" => ["id_profil" => (int) $pdo->lastInsertId(), "nom" => $nom, "prenom" => $prenom, "emailPro" => $emailPro, "role" => $role],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création : " . $e->getMessage()]);
}