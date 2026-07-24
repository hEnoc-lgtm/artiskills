<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

$select = "
    SELECT t.idTest, t.date, t.statutTest, t.score, t.heureDebut, t.heureFin, t.statutAffectation,
           t.id_artisan, a.nom, a.prenom
    FROM test t JOIN artisan a ON a.id_artisan = t.id_artisan
";

try {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare($select . " WHERE t.idTest = :id");
        $stmt->execute(["id" => $_GET['id']]);
        $test = $stmt->fetch();

        if (!$test) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Test introuvable."]);
            exit;
        }

        echo json_encode(["success" => true, "data" => $test]);
    } elseif (isset($_GET['id_artisan'])) {
        $stmt = $pdo->prepare($select . " WHERE t.id_artisan = :id_artisan");
        $stmt->execute(["id_artisan" => $_GET['id_artisan']]);
        $test = $stmt->fetch();

        if (!$test) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Aucun test trouvé pour cet artisan."]);
            exit;
        }

        echo json_encode(["success" => true, "data" => $test]);
    } elseif (isset($_GET['statutTest'])) {
        $stmt = $pdo->prepare($select . " WHERE t.statutTest = :statut ORDER BY t.date DESC");
        $stmt->execute(["statut" => $_GET['statutTest']]);
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    } else {
        $stmt = $pdo->query($select . " ORDER BY t.date DESC");
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la lecture : " . $e->getMessage()]);
}
