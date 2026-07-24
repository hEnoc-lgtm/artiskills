<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$id_profil = $donnees['id_profil'] ?? null;

if (!$id_profil) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "L'identifiant 'id_profil' est obligatoire pour la modification."]);
    exit;
}

$nom = trim($donnees['nom'] ?? '');
$prenom = trim($donnees['prenom'] ?? '');
$contact = trim($donnees['contact'] ?? '');
$sexe = $donnees['sexe'] ?? '';
$emailPro = trim($donnees['emailPro'] ?? '');
$service = trim($donnees['service'] ?? '');
$role = $donnees['role'] ?? '';
$motdepasse = $donnees['motdepasse'] ?? ''; // Optionnel lors de la modification

// Validation des valeurs d'ENUM strictes de votre base de données
if ($sexe !== '' && !in_array($sexe, ['Masculin', 'Féminin'], true)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "sexe doit être 'Masculin' ou 'Féminin'."]);
    exit;
}
if ($role !== '' && !in_array($role, ['agent simple', 'administratueur'], true)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "role doit être 'agent simple' ou 'administratueur'."]);
    exit;
}

try {
    // Vérifier si la nouvelle adresse e-mail modifiée n'est pas déjà prise par un AUTRE agent
    $verifEmail = $pdo->prepare("SELECT id_profil FROM profil WHERE emailPro = :email AND id_profil != :id");
    $verifEmail->execute(["email" => $emailPro, "id" => $id_profil]);
    if ($verifEmail->fetch()) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Cette adresse e-mail professionnelle appartient déjà à un autre profil."]);
        exit;
    }

    // Si l'administrateur a saisi un nouveau mot de passe, on le hache et on met à jour toute la ligne
    if ($motdepasse !== '') {
        $stmt = $pdo->prepare("
            UPDATE profil 
            SET nom = :nom, prenom = :prenom, contact = :contact, sexe = :sexe, 
                motDepasse = :mdp, emailPro = :emailPro, service = :service, role = :role 
            WHERE id_profil = :id
        ");
        $params = [
            "nom" => $nom, "prenom" => $prenom, "contact" => $contact, "sexe" => $sexe,
            "mdp" => password_hash($motdepasse, PASSWORD_DEFAULT),
            "emailPro" => $emailPro, "service" => $service, "role" => $role, "id" => $id_profil
        ];
    } else {
        // Sinon, on met à jour les infos sans toucher au mot de passe actuel en base de données
        $stmt = $pdo->prepare("
            UPDATE profil 
            SET nom = :nom, prenom = :prenom, contact = :contact, sexe = :sexe, 
                emailPro = :emailPro, service = :service, role = :role 
            WHERE id_profil = :id
        ");
        $params = [
            "nom" => $nom, "prenom" => $prenom, "contact" => $contact, "sexe" => $sexe,
            "emailPro" => $emailPro, "service" => $service, "role" => $role, "id" => $id_profil
        ];
    }

    $stmt->execute($params);
    echo json_encode(["success" => true, "message" => "Compte utilisateur mis à jour avec succès dans le CRUD."]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la modification : " . $e->getMessage()]);
}
