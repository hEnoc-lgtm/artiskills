<?php
/**
 * Headers communs à toutes les routes de l'API
 * -----------------------------------------------
 * À inclure (require) tout en haut de chaque fichier API,
 * AVANT toute sortie (echo, espace, etc.)
 */

// Autorise les requêtes depuis le frontend React (en développement)
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// Le navigateur envoie une requête OPTIONS avant certaines requêtes
// (preflight). On répond simplement OK sans traitement.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}