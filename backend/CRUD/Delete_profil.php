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

if (!$id_profil) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "L'identifiant du profil est requis."]);
    exit;
}

try {
    // Vérifier si le profil existe
    $verif = $pdo->prepare("SELECT * FROM profil WHERE id_profil = :id");
    $verif->execute(["id" => $id_profil]);
    $profil = $verif->fetch(PDO::FETCH_ASSOC);

    if (!$profil) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Profil introuvable."]);
        exit;
    }

    // Supprimer le profil
    $stmt = $pdo->prepare("DELETE FROM profil WHERE id_profil = :id");
    $stmt->execute(["id" => $id_profil]);

    echo json_encode([
        "success" => true,
        "message" => "Profil supprimé avec succès.",
        "data" => ["id_profil" => (int) $id_profil]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la suppression : " . $e->getMessage()]);
}
?>