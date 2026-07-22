<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$select = "
    SELECT af.idAffect, af.distanceCalculee, af.adresseReference, af.dateAffectation, af.statutPlace,
           af.idTest, af.idCentre, cf.nomCentre
    FROM affectation af JOIN centre_formation cf ON cf.idCentre = af.idCentre
";

try {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare($select . " WHERE af.idAffect = :id");
        $stmt->execute(["id" => $_GET['id']]);
        $affectation = $stmt->fetch();

        if (!$affectation) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Affectation introuvable."]);
            exit;
        }

        echo json_encode(["success" => true, "data" => $affectation]);
        exit;
    }

    if (isset($_GET['idTest'])) {
        $stmt = $pdo->prepare($select . " WHERE af.idTest = :idTest");
        $stmt->execute(["idTest" => $_GET['idTest']]);
        $affectation = $stmt->fetch();

        if (!$affectation) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Aucune affectation trouvée pour ce test."]);
            exit;
        }

        echo json_encode(["success" => true, "data" => $affectation]);
        exit;
    }

    // Filtres combinables : idCentre, statutPlace
    $conditions = [];
    $parametres = [];

    if (isset($_GET['idCentre'])) {
        $conditions[] = "af.idCentre = :idCentre";
        $parametres['idCentre'] = $_GET['idCentre'];
    }
    if (isset($_GET['statutPlace'])) {
        $conditions[] = "af.statutPlace = :statutPlace";
        $parametres['statutPlace'] = $_GET['statutPlace'];
    }

    $sql = $select;
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    $sql .= " ORDER BY af.dateAffectation DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametres);
    echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la lecture : " . $e->getMessage()]);
}