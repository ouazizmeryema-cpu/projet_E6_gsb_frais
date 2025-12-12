<?php
require_once 'config/config.php';

if (!isAuthenticated()) {
    header('Location: /login.php');
    exit;
}

$user = getCurrentUser();
$action = $_GET['action'] ?? 'dashboard';
$controller = null;

switch ($user['type']) {
    case 'visiteur':
        require_once 'controllers/VisiteurController.php';
        $controller = new VisiteurController();
        
        if ($action === 'save_frais_forfait') {
            $controller->saveFraisForfait();
        } elseif ($action === 'add_frais_hors_forfait') {
            $controller->addFraisHorsForfait();
        } elseif ($action === 'delete_frais_hors_forfait') {
            $controller->deleteFraisHorsForfait();
        } elseif ($action === 'cloturer_mois') {
            $controller->cloturerMois();
        } else {
            $controller->dashboard();
        }
        break;
        
    case 'comptable':
        require_once 'controllers/ComptableController.php';
        $controller = new ComptableController();
        
        if ($action === 'voir_fiche') {
            $controller->voirFiche();
        } elseif ($action === 'valider_fiche') {
            $controller->validerFiche();
        } elseif ($action === 'refuser_fiche') {
            $controller->refuserFiche();
        } elseif ($action === 'valider_frais_hors_forfait') {
            $controller->validerFraisHorsForfait();
        } elseif ($action === 'refuser_frais_hors_forfait') {
            $controller->refuserFraisHorsForfait();
        } else {
            $controller->dashboard();
        }
        break;
        
    case 'admin':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->dashboard();
        break;
        
    default:
        header('Location: /login.php');
        exit;
}
