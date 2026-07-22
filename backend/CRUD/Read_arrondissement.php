<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

// Note : longitude/latitude ne sont plus sur arrondissement, elles sont
// désormais sur quartier_village (schéma v3).
$select = "
    SELECT a.id_arrondissement, a.nom_arrondissement, a.idCommune, c.nomCommune, d.nomDepartement
    FROM arrondissement a
    JOIN commune c ON c.idCommune = a.idCommune
    JOIN departement d ON d.idDepart = c.idDepart
";

try {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare($select . " WHERE a.id_arrondissement = :id");
        $stmt->execute(["id" => $_GET['id']]);
        $arrondissement = $stmt->fetch();

        if (!$arrondissement) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Arrondissement introuvable."]);
            exit;
        }

        echo json_encode(["success" => true, "data" => $arrondissement]);
    } elseif (isset($_GET['idCommune'])) {
        $stmt = $pdo->prepare("
            SELECT id_arrondissement, nom_arrondissement, idCommune
            FROM arrondissement WHERE idCommune = :idCommune ORDER BY nom_arrondissement ASC
        ");
        $stmt->execute(["idCommune" => $_GET['idCommune']]);
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    } else {
        $stmt = $pdo->query($select . " ORDER BY d.nomDepartement ASC, c.nomCommune ASC, a.nom_arrondissement ASC");
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la lecture : " . $e->getMessage()]);
}