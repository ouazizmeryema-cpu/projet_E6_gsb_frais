<?php
/**
 * Configuration générale de l'application
 */

// DÃ©marrer la session uniquement si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration de l'application
define('APP_NAME', 'GSB Frais');
define('APP_VERSION', '1.0.0');

// Chemins absolus sur le serveur
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');

// Détection automatique du chemin de base de l'application (fonctionne en sous-dossier ou à la racine)
if (!defined('BASE_URL')) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    define('BASE_URL', rtrim($scriptDir, '/'));
}

// Créer le dossier uploads s'il n'existe pas
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

// Inclusion de la base de données
require_once ROOT_PATH . '/config/database.php';

/**
 * Retourne une URL relative au chemin de base de l'application.
 */
function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Redirige vers une URL relative à la racine de l'application.
 */
function redirect($path = 'index.php') {
    header('Location: ' . url($path));
    exit;
}

/**
 * Vérifie si l'utilisateur est authentifié
 *.
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

/**
 * Vérifie le type d'utilisateur. Redirige si non autorisé.
 */
function checkUserType($allowedTypes) {
    if (!isAuthenticated()) {
        redirect('login.php');
    }
    if (!in_array($_SESSION['user_type'], (array)$allowedTypes)) {
        redirect('index.php');
    }
}

/**
 * Retourne les informations de l'utilisateur connecté.
 */
function getCurrentUser() {
    if (!isAuthenticated()) {
        return null;
    }
    return [
        'id'     => $_SESSION['user_id'],
        'nom'    => $_SESSION['user_nom'],
        'prenom' => $_SESSION['user_prenom'],
        'type'   => $_SESSION['user_type'],
    ];
}
