<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);

// Récupération des données (avec gestion de la casse camelCase envoyée par React)
$nom = trim($donnees['nom'] ?? '');
$prenom = trim($donnees['prenom'] ?? '');
$contact = trim(preg_replace('/\s+/', '', $donnees['contact'] ?? '')); // Supprime les espaces
$sexe = $donnees['sexe'] ?? '';
$motDePasse = $donnees['motDePasse'] ?? '';
$emailPro = trim($donnees['emailPro'] ?? '');
$service = trim($donnees['service'] ?? '');
$role = $donnees['role'] ?? '';

// 1. Validation des champs STRICTEMENT obligatoires
// Note : 'service' est OPTIONNEL, on ne le vérifie pas ici
if ($nom === '' || $prenom === '' || $contact === '' || $sexe === '' || $motDePasse === '' || $emailPro === '' || $role === '') {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Veuillez remplir tous les champs obligatoires (Nom, Prénom, Contact, Sexe, Email, Mot de passe, Rôle)."]);
    exit;
}

// 2. Validation des ENUMs de la base de données
if (!in_array($sexe, ['Masculin', 'Féminin'], true)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Le sexe doit être 'Masculin' ou 'Féminin'."]);
    exit;
}

if (!in_array($role, ['agent simple', 'administratueur'], true)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Le rôle doit être 'agent simple' ou 'administratueur'."]);
    exit;
}

// 3. Validation du format email
if (!filter_var($emailPro, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "L'adresse e-mail n'est pas valide."]);
    exit;
}

// 4. Validation du format contact (numéro béninois : 8 chiffres commençant par un chiffre)
if (!preg_match('/^[0-9]{8,15}$/', $contact)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Le numéro de contact doit contenir entre 8 et 15 chiffres."]);
    exit;
}

try {
    // 5. Vérification des doublons (Email)
    $verifEmail = $pdo->prepare("SELECT id_profil FROM profil WHERE emailPro = :emailPro");
    $verifEmail->execute(["emailPro" => $emailPro]);
    if ($verifEmail->fetch()) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Cette adresse e-mail professionnelle est déjà utilisée par un autre compte."]);
        exit;
    }

    // 6. Vérification des doublons (Contact)
    $verifContact = $pdo->prepare("SELECT id_profil FROM profil WHERE contact = :contact");
    $verifContact->execute(["contact" => $contact]);
    if ($verifContact->fetch()) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Ce numéro de contact est déjà associé à un autre compte."]);
        exit;
    }

    // 7. Insertion en base de données
    // Note : dateCreation est géré par DEFAULT CURRENT_TIMESTAMP dans la BDD
    $stmt = $pdo->prepare("
        INSERT INTO profil (nom, prenom, contact, sexe, motDepasse, emailPro, service, role)
        VALUES (:nom, :prenom, :contact, :sexe, :motDepasse, :emailPro, :service, :role)
    ");
    
    $stmt->execute([
        "nom" => $nom,
        "prenom" => $prenom,
        "contact" => $contact,
        "sexe" => $sexe,
        "motDepasse" => password_hash($motDePasse, PASSWORD_DEFAULT),
        "emailPro" => $emailPro,
        "service" => $service ?: null, // NULL si vide
        "role" => $role,
    ]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Profil créé avec succès.",
        "data" => [
            "id_profil" => (int) $pdo->lastInsertId(), 
            "nom" => $nom, 
            "prenom" => $prenom, 
            "emailPro" => $emailPro, 
            "role" => $role
        ],
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création : " . $e->getMessage()]);
}
?>