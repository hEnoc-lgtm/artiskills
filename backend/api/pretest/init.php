<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/database.php';

try {
    // Récupérer tous les départements
    $stmtDeps = $pdo->query("SELECT idDepart, nomDepartement FROM departement ORDER BY nomDepartement ASC");
    $departements = $stmtDeps->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer tous les corps de métier
    $stmtMetiers = $pdo->query("SELECT code_corpsmetier, libelle FROM corps_metier ORDER BY libelle ASC");
    $corpsMetiers = $stmtMetiers->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "departements" => $departements,
        "corpsMetiers" => $corpsMetiers
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erreur de chargement des données initiales: " . $e->getMessage()
    ]);
}
?>