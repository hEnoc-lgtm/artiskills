<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/osm.php'; // ← AJOUT : Import des fonctions OSM

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    exit;
}

$donnees = json_decode(file_get_contents("php://input"), true);
$idCentre = $donnees['idCentre'] ?? null;

if (!$idCentre) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "L'identifiant 'idCentre' est obligatoire."]);
    exit;
}

try {
    // ═══════════════════════════════════════════════════════════════
    // 🌍 GÉOLOCALISATION AUTOMATIQUE VIA OPENSTREETMAP
    // Si le nouveau quartier n'a pas encore de coordonnées, on les récupère via Nominatim
    // ═══════════════════════════════════════════════════════════════
    if (!empty($donnees['id_quartier_centre'])) {
        assurerCoordonneesQuartier($pdo, (int)$donnees['id_quartier_centre']);
    }
    // ═══════════════════════════════════════════════════════════════

    // Mettre à jour les informations réelles
    $stmt = $pdo->prepare("
        UPDATE centre_formation 
        SET nomCentre = :nom, contactCentre = :contact, id_quartier_centre = :idQuartier
        WHERE idCentre = :id
    ");

    $stmt->execute([
        "nom" => $donnees['nomCentre'],
        "contact" => $donnees['contactCentre'],
        "idQuartier" => (int)$donnees['id_quartier_centre'],
        "id" => $idCentre
    ]);

    echo json_encode(["success" => true, "message" => "Fiche du centre de formation mise à jour."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la modification : " . $e->getMessage()]);
}
?>