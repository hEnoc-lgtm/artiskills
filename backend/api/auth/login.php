<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée. Utilisez POST."]);
    exit;
}

$configPath = __DIR__ . '/../../config/database.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Fichier database.php introuvable."]);
    exit;
}

require_once $configPath;

$input = file_get_contents("php://input");
$donnees = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Données JSON invalides."]);
    exit;
}

$identifiant = trim($donnees['identifiant'] ?? '');
$motDePasse = trim($donnees['motDePasse'] ?? '');

if (!$identifiant || !$motDePasse) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "L'identifiant et le mot de passe sont obligatoires."]);
    exit;
}

try {
    if (!isset($pdo)) {
        throw new Exception("La variable \$pdo n'est pas définie dans database.php");
    }

    // ✅ CORRECTION : Deux paramètres distincts au lieu d'un seul réutilisé
    $stmt = $pdo->prepare("SELECT id_profil, nom, prenom, role, motDepasse FROM profil WHERE emailPro = :identifiant1 OR contact = :identifiant2");
    $stmt->execute([
        'identifiant1' => $identifiant,
        'identifiant2' => $identifiant
    ]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $motDePasseValide = password_verify($motDePasse, $user['motDepasse']) || ($motDePasse === $user['motDepasse']);

        if ($motDePasseValide) {
            $updateStmt = $pdo->prepare("UPDATE profil SET dernierAcces = NOW() WHERE id_profil = :id");
            $updateStmt->execute(['id' => $user['id_profil']]);

            echo json_encode([
                "success" => true,
                "message" => "Connexion réussie.",
                "data" => [
                    "id_profil" => (int)$user['id_profil'],
                    "nom" => trim($user['nom'] . ' ' . $user['prenom']),
                    "role" => $user['role']
                ]
            ]);
            exit;
        }
    }

    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Identifiant ou mot de passe incorrect."]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur Base de Données : " . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur Serveur : " . $e->getMessage()]);
}
?>