<?php
/**
 * Configuration générale de l'application
 */
session_start();

// Configuration de l'application
define('APP_NAME', 'GSB Frais');
define('APP_VERSION', '1.0.0');

// Chemins
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('UPLOAD_URL', '/uploads');

// Créer le dossier uploads s'il n'existe pas
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
}

// Inclusion de la base de données
require_once ROOT_PATH . '/config/database.php';

// Fonction pour vérifier l'authentification
function isAuthenticated() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

// Fonction pour vérifier le type d'utilisateur
function checkUserType($allowedTypes) {
    if (!isAuthenticated()) {
        header('Location: /login.php');
        exit;
    }
    
    if (!in_array($_SESSION['user_type'], $allowedTypes)) {
        header('Location: /index.php');
        exit;
    }
}

// Fonction pour obtenir l'utilisateur connecté
function getCurrentUser() {
    if (!isAuthenticated()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'nom' => $_SESSION['user_nom'],
        'prenom' => $_SESSION['user_prenom'],
        'type' => $_SESSION['user_type']
    ];
}

