<?php
require_once 'config/config.php';

if (!isAuthenticated()) {
    redirect('login.php');
}

$user   = getCurrentUser();
$action = $_GET['action'] ?? 'dashboard';

switch ($user['type']) {
    case 'visiteur':
        require_once 'controllers/VisiteurController.php';
        $controller = new VisiteurController();

        switch ($action) {
            case 'save_frais_forfait':
                $controller->saveFraisForfait();
                break;
            case 'add_frais_hors_forfait':
                $controller->addFraisHorsForfait();
                break;
            case 'delete_frais_hors_forfait':
                $controller->deleteFraisHorsForfait();
                break;
            case 'cloturer_mois':
                $controller->cloturerMois();
                break;
            case 'graphique':
                $controller->graphique();
                break;
            default:
                $controller->dashboard();
        }
        break;

    case 'comptable':
        require_once 'controllers/ComptableController.php';
        $controller = new ComptableController();

        switch ($action) {
            case 'voir_fiche':
                $controller->voirFiche();
                break;
            case 'valider_fiche':
                $controller->validerFiche();
                break;
            case 'refuser_fiche':
                $controller->refuserFiche();
                break;
            case 'valider_frais_hors_forfait':
                $controller->validerFraisHorsForfait();
                break;
            case 'refuser_frais_hors_forfait':
                $controller->refuserFraisHorsForfait();
                break;
            case 'payer_fiche':
                $controller->payerFiche(); 
                break;
            default:
                $controller->dashboard();
        }
        break;

    case 'admin':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();

        switch ($action) {
            case 'add_user':
                $controller->addUser();
                break;
            case 'edit_user':
                $controller->editUser();
                break;
            case 'toggle_user':         
                $controller->toggleUser();
                break;
            case 'add_forfait':
                $controller->addForfait();
                break;
            case 'edit_forfait':
                $controller->editForfait();
                break;
            case 'delete_forfait':
                $controller->deleteForfait();
                break;
            default:
                $controller->dashboard();
        }
        break;

    default:
        redirect('login.php');
}