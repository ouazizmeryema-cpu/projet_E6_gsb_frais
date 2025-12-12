<?php
require_once __DIR__ . '/../models/FicheFrais.php';
require_once __DIR__ . '/../models/FraisForfait.php';
require_once __DIR__ . '/../models/FraisHorsForfait.php';

class VisiteurController {
    private $ficheFraisModel;
    private $fraisForfaitModel;
    private $fraisHorsForfaitModel;
    
    public function __construct() {
        $this->ficheFraisModel = new FicheFrais();
        $this->fraisForfaitModel = new FraisForfait();
        $this->fraisHorsForfaitModel = new FraisHorsForfait();
    }
    
    public function dashboard() {
        $idVisiteur = $_SESSION['user_id'];
        $moisActuel = date('Ym');
        
        // Créer la fiche du mois si elle n'existe pas
        $this->ficheFraisModel->createFiche($idVisiteur, $moisActuel);
        
        $fiche = $this->ficheFraisModel->getFicheByMois($idVisiteur, $moisActuel);
        $fraisForfaits = $this->fraisForfaitModel->getAll();
        $lignesForfait = $this->fraisForfaitModel->getLignesByMois($idVisiteur, $moisActuel);
        $fraisHorsForfait = $this->fraisHorsForfaitModel->getByMois($idVisiteur, $moisActuel);
        $historique = $this->ficheFraisModel->getFichesByVisiteur($idVisiteur);
        
        require_once __DIR__ . '/../views/visiteur/dashboard.php';
    }
    
    public function saveFraisForfait() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /index.php');
            exit;
        }
        
        $idVisiteur = $_SESSION['user_id'];
        $mois = $_POST['mois'] ?? date('Ym');
        
        $fraisForfaits = $this->fraisForfaitModel->getAll();
        
        foreach ($fraisForfaits as $frais) {
            $quantite = intval($_POST['quantite_' . $frais['id']] ?? 0);
            $this->fraisForfaitModel->saveLigne($idVisiteur, $frais['id'], $mois, $quantite);
        }
        
        header('Location: /index.php?success=1');
        exit;
    }
    
    public function addFraisHorsForfait() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /index.php');
            exit;
        }
        
        $idVisiteur = $_SESSION['user_id'];
        $mois = $_POST['mois'] ?? date('Ym');
        $dateFrais = $_POST['date_frais'];
        $libelle = $_POST['libelle'];
        $montant = floatval($_POST['montant']);
        
        $justificatif = null;
        if (isset($_FILES['justificatif']) && $_FILES['justificatif']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/';
            $fileName = uniqid() . '_' . basename($_FILES['justificatif']['name']);
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['justificatif']['tmp_name'], $targetFile)) {
                $justificatif = $fileName;
            }
        }
        
        $this->fraisHorsForfaitModel->create($idVisiteur, $mois, $dateFrais, $libelle, $montant, $justificatif);
        
        header('Location: /index.php?success=1');
        exit;
    }
    
    public function deleteFraisHorsForfait() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /index.php');
            exit;
        }
        
        $id = intval($_POST['id']);
        $idVisiteur = $_SESSION['user_id'];
        
        $this->fraisHorsForfaitModel->delete($id, $idVisiteur);
        
        header('Location: /index.php?success=1');
        exit;
    }
    
    public function cloturerMois() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /index.php');
            exit;
        }
        
        $idVisiteur = $_SESSION['user_id'];
        $mois = $_POST['mois'] ?? date('Ym');
        
        $this->ficheFraisModel->cloturerFiche($idVisiteur, $mois);
        
        header('Location: /index.php?success=1');
        exit;
    }
}

