<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$id_profil = $donnees['id_profil'] ?? null;
$nom = trim($donnees['nom'] ?? '');
$prenom = trim($donnees['prenom'] ?? '');
$contact = trim($donnees['contact'] ?? '');
$sexe = $donnees['sexe'] ?? '';
$emailPro = trim($donnees['emailPro'] ?? '');
$service = trim($donnees['service'] ?? '');
$role = $donnees['role'] ?? '';
$motDePasse = $donnees['motDePasse'] ?? '';

if (!$id_profil || $nom === '' || $prenom === '' || $contact === '' || $sexe === '' || $emailPro === '' || $service === '' || $role === '') {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Tous les champs obligatoires sont requis."]);
    exit;
}

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

try {
    // Vérifier si le profil existe
    $verif = $pdo->prepare("SELECT id_profil FROM profil WHERE id_profil = :id");
    $verif->execute(["id" => $id_profil]);
    if (!$verif->fetch()) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Profil introuvable."]);
        exit;
    }

    // Vérifier si l'email existe déjà (sauf pour ce profil)
    $verifEmail = $pdo->prepare("SELECT id_profil FROM profil WHERE emailPro = :email AND id_profil != :id");
    $verifEmail->execute(["email" => $emailPro, "id" => $id_profil]);
    if ($verifEmail->fetch()) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Cette adresse e-mail est déjà utilisée par un autre compte."]);
        exit;
    }

    // Vérifier si le contact existe déjà (sauf pour ce profil)
    $verifContact = $pdo->prepare("SELECT id_profil FROM profil WHERE contact = :contact AND id_profil != :id");
    $verifContact->execute(["contact" => $contact, "id" => $id_profil]);
    if ($verifContact->fetch()) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Ce numéro de contact est déjà utilisé par un autre compte."]);
        exit;
    }

    // Mise à jour
    if (!empty($motDePasse)) {
        // Avec nouveau mot de passe
        $stmt = $pdo->prepare("
            UPDATE profil 
            SET nom = :nom, prenom = :prenom, contact = :contact, sexe = :sexe, 
                emailPro = :emailPro, service = :service, role = :role, 
                motDepasse = :motDepasse
            WHERE id_profil = :id
        ");
        $stmt->execute([
            "nom" => $nom,
            "prenom" => $prenom,
            "contact" => $contact,
            "sexe" => $sexe,
            "emailPro" => $emailPro,
            "service" => $service,
            "role" => $role,
            "motDepasse" => password_hash($motDePasse, PASSWORD_DEFAULT),
            "id" => $id_profil
        ]);
    } else {
        // Sans changer le mot de passe
        $stmt = $pdo->prepare("
            UPDATE profil 
            SET nom = :nom, prenom = :prenom, contact = :contact, sexe = :sexe, 
                emailPro = :emailPro, service = :service, role = :role
            WHERE id_profil = :id
        ");
        $stmt->execute([
            "nom" => $nom,
            "prenom" => $prenom,
            "contact" => $contact,
            "sexe" => $sexe,
            "emailPro" => $emailPro,
            "service" => $service,
            "role" => $role,
            "id" => $id_profil
        ]);
    }

    echo json_encode([
        "success" => true,
        "message" => "Profil modifié avec succès.",
        "data" => ["id_profil" => (int) $id_profil, "nom" => $nom, "prenom" => $prenom]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la modification : " . $e->getMessage()]);
}
?>