<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'], true)) {
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
$motdepasse = $donnees['motdepasse'] ?? null; // optionnel : ne change le mot de passe que si fourni

if (!$id_profil || $nom === '' || $prenom === '' || $contact === '' || $sexe === '' || $emailPro === '' || $service === '' || $role === '') {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Tous les champs sont obligatoires."]);
    exit;
}

if (!in_array($sexe, ['Masculin', 'Féminin'], true)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "sexe doit être 'Masculin' ou 'Féminin'."]);
    exit;
}

if (!in_array($role, ['agent simple', 'administratueur'], true)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "role doit être 'agent simple' ou 'administratueur'."]);
    exit;
}

try {
    if ($motdepasse !== null && $motdepasse !== '') {
        $stmt = $pdo->prepare("
            UPDATE profil SET nom = :nom, prenom = :prenom, contact = :contact, sexe = :sexe,
                emailPro = :emailPro, service = :service, role = :role, motDepasse = :motDepasse
            WHERE id_profil = :id_profil
        ");
        $stmt->execute([
            "nom" => $nom, "prenom" => $prenom, "contact" => $contact, "sexe" => $sexe,
            "emailPro" => $emailPro, "service" => $service, "role" => $role,
            "motDepasse" => password_hash($motdepasse, PASSWORD_DEFAULT), "id_profil" => $id_profil,
        ]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE profil SET nom = :nom, prenom = :prenom, contact = :contact, sexe = :sexe,
                emailPro = :emailPro, service = :service, role = :role
            WHERE id_profil = :id_profil
        ");
        $stmt->execute([
            "nom" => $nom, "prenom" => $prenom, "contact" => $contact, "sexe" => $sexe,
            "emailPro" => $emailPro, "service" => $service, "role" => $role, "id_profil" => $id_profil,
        ]);
    }

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Profil introuvable ou aucune modification effectuée."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Profil mis à jour avec succès."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la mise à jour : " . $e->getMessage()]);
}