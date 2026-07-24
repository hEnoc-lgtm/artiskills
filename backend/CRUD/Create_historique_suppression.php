<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit;
}

try {
    // Lecture des suppressions de la plus récente à la plus ancienne
    $stmt = $pdo->query("
        SELECT 
            hs.id_historique_sup,
            hs.enonceSup,
            hs.reponsesSup,
            hs.dateSuppression,
            hs.heureSuppression,
            p.login as nom_admin
        FROM historique_suppression hs
        LEFT JOIN profil p ON hs.id_admin = p.idProfil
        ORDER BY hs.dateSuppression DESC, hs.heureSuppression DESC
    ");
    
    $suppressions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "data" => $suppressions]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur de lecture de la boîte noire : " . $e->getMessage()]);
}
