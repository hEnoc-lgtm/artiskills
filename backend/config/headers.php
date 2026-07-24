<?php
// 1. Autoriser l'origine de votre application React (Vite utilise par défaut le port 5173)
// ⚠️ Si votre terminal React affiche un autre port (ex: 5174 ou 3000), changez-le ici.
header("Access-Control-Allow-Origin: http://localhost:5173");

// 2. Autoriser les méthodes HTTP que votre application est susceptible d'utiliser
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");

// 3. Autoriser les en-têtes (headers) spécifiques envoyés par React (surtout Content-Type pour le JSON)
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// 4. Indiquer que toutes les réponses de ce script seront au format JSON
header("Content-Type: application/json; charset=UTF-8");

// 5. Gérer la requête de pré-vérification (Preflight) du navigateur
// C'est l'étape la plus importante : le navigateur envoie une requête "OPTIONS" avant le "POST".
// Si on ne répond pas 200 OK ici, le navigateur bloque tout et affiche "Failed to fetch".
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit; // On arrête le script immédiatement après avoir envoyé les headers
}
?>