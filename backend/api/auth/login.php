<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$identifiant = trim($donnees['identifiant'] ?? '');
$motDePasse = trim($donnees['motDePasse'] ?? '');

if (!$identifiant || !$motDePasse) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "L'identifiant et le mot de passe sont obligatoires."]);
    exit;
}

try {
    // On cherche l'utilisateur par son emailPro OU son numéro de contact 
    // (puisque le champ s'appelle "Identifiant ou Matricule" dans le formulaire)
    $stmt = $pdo->prepare("SELECT id_profil, nom, prenom, role, motDepasse FROM profil WHERE emailPro = :identifiant OR contact = :identifiant");
    $stmt->execute(['identifiant' => $identifiant]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Vérification du mot de passe. 
        // NOTE : On utilise password_verify pour les mots de passe hachés, 
        // mais on garde une comparaison directe (===) en secours pour faciliter vos tests en local si la BDD n'est pas encore hachée.
        $motDePasseValide = password_verify($motDePasse, $user['motDepasse']) || ($motDePasse === $user['motDepasse']);

        if ($motDePasseValide) {
            // Mise à jour de la dernière connexion (optionnel mais recommandé)
            $updateStmt = $pdo->prepare("UPDATE profil SET dernierAcces = NOW() WHERE id_profil = :id");
            $updateStmt->execute(['id' => $user['id_profil']]);

            echo json_encode([
                "success" => true,
                "message" => "Connexion réussie.",
                "data" => [
                    "id_profil" => (int)$user['id_profil'],
                    "nom" => trim($user['nom'] . ' ' . $user['prenom']),
                    "role" => $user['role'] // Sera 'agent simple' ou 'administratueur' selon votre ENUM
                ]
            ]);
            exit;
        }
    }

    // Message générique pour ne pas révéler si c'est l'identifiant ou le mot de passe qui est faux
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Identifiant ou mot de passe incorrect."]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur serveur : " . $e->getMessage()]);
}
?>