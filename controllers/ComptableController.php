<?php
require_once __DIR__ . '/../models/FicheFrais.php';
require_once __DIR__ . '/../models/FraisForfait.php';
require_once __DIR__ . '/../models/FraisHorsForfait.php';
require_once __DIR__ . '/../models/User.php';

class ComptableController {
    private $ficheFraisModel;
    private $fraisForfaitModel;
    private $fraisHorsForfaitModel;
    private $userModel;
    
    public function __construct() {
        $this->ficheFraisModel = new FicheFrais();
        $this->fraisForfaitModel = new FraisForfait();
        $this->fraisHorsForfaitModel = new FraisHorsForfait();
        $this->userModel = new User();
    }
    
    public function dashboard() {
        $fichesEnAttente = $this->ficheFraisModel->getAllFichesEnAttente();
        $visiteurs = $this->userModel->getAllVisiteurs();
        
        require_once __DIR__ . '/../views/comptable/dashboard.php';
    }
    
    public function voirFiche() {
        $idVisiteur = intval($_GET['visiteur'] ?? 0);
        $moisInput = $_GET['mois'] ?? '';
        
        // Convertir le format YYYY-MM en YYYYMM
        if (strlen($moisInput) === 7 && strpos($moisInput, '-') !== false) {
            $mois = str_replace('-', '', $moisInput);
        } else {
            $mois = $moisInput;
        }
        
        if (!$idVisiteur || !$mois) {
            header('Location: /index.php');
            exit;
        }
        
        $fiche = $this->ficheFraisModel->getFicheByMois($idVisiteur, $mois);
        $lignesForfait = $this->fraisForfaitModel->getLignesByMois($idVisiteur, $mois);
        $fraisHorsForfait = $this->fraisHorsForfaitModel->getByMois($idVisiteur, $mois);
        $visiteur = $this->userModel->getById($idVisiteur);
        
        require_once __DIR__ . '/../views/comptable/voir_fiche.php';
    }
    
    public function validerFiche() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /index.php');
            exit;
        }
        
        $idVisiteur = intval($_POST['id_visiteur']);
        $mois = $_POST['mois'];
        
        $this->ficheFraisModel->validerFiche($idVisiteur, $mois);
        
        header('Location: /index.php?success=1');
        exit;
    }
    
    public function refuserFiche() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /index.php');
            exit;
        }
        
        $idVisiteur = intval($_POST['id_visiteur']);
        $mois = $_POST['mois'];
        
        $this->ficheFraisModel->refuserFiche($idVisiteur, $mois);
        
        header('Location: /index.php?success=1');
        exit;
    }
    
    public function validerFraisHorsForfait() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /index.php');
            exit;
        }
        
        $id = intval($_POST['id']);
        $commentaire = $_POST['commentaire'] ?? null;
        
        $this->fraisHorsForfaitModel->valider($id, $commentaire);
        
        header('Location: /index.php?action=voir_fiche&visiteur=' . $_POST['id_visiteur'] . '&mois=' . $_POST['mois']);
        exit;
    }
    
    public function refuserFraisHorsForfait() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /index.php');
            exit;
        }
        
        $id = intval($_POST['id']);
        $commentaire = $_POST['commentaire'] ?? '';
        
        if (empty($commentaire)) {
            header('Location: /index.php?action=voir_fiche&visiteur=' . $_POST['id_visiteur'] . '&mois=' . $_POST['mois'] . '&error=commentaire_requis');
            exit;
        }
        
        $this->fraisHorsForfaitModel->refuser($id, $commentaire);
        
        header('Location: /index.php?action=voir_fiche&visiteur=' . $_POST['id_visiteur'] . '&mois=' . $_POST['mois']);
        exit;
    }
}

