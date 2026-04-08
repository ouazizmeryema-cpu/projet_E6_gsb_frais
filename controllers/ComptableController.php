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
        checkUserType('comptable');
        $this->ficheFraisModel       = new FicheFrais();
        $this->fraisForfaitModel     = new FraisForfait();
        $this->fraisHorsForfaitModel = new FraisHorsForfait();
        $this->userModel             = new User();
    }

    public function dashboard() {
        $fichesEnAttente = $this->ficheFraisModel->getAllFichesEnAttente();
        $visiteurs       = $this->userModel->getAllVisiteurs();

        require_once __DIR__ . '/../views/comptable/dashboard.php';
    }

    public function voirFiche() {
        $idVisiteur = intval($_GET['visiteur'] ?? 0);
        $moisInput  = $_GET['mois'] ?? '';

        // Accepte les formats YYYYMM et YYYY-MM
        if (strlen($moisInput) === 7 && strpos($moisInput, '-') !== false) {
            $mois = str_replace('-', '', $moisInput);
        } else {
            $mois = $moisInput;
        }

        if (!$idVisiteur || !preg_match('/^\d{6}$/', $mois)) {
            redirect('index.php?error=parametres_invalides');
        }

        $fiche            = $this->ficheFraisModel->getFicheByMois($idVisiteur, $mois);
        $lignesForfait    = $this->fraisForfaitModel->getLignesByMois($idVisiteur, $mois);
        $fraisHorsForfait = $this->fraisHorsForfaitModel->getByMois($idVisiteur, $mois);
        $visiteur         = $this->userModel->getById($idVisiteur);

        if (!$visiteur) {
            redirect('index.php?error=visiteur_introuvable');
        }

        require_once __DIR__ . '/../views/comptable/voir_fiche.php';
    }

    public function validerFiche() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php');
        }

        $idVisiteur = intval($_POST['id_visiteur'] ?? 0);
        $mois       = $_POST['mois'] ?? '';

        if ($idVisiteur && preg_match('/^\d{6}$/', $mois)) {
            $this->ficheFraisModel->validerFiche($idVisiteur, $mois);
        }

        redirect('index.php?success=1');
    }

    public function refuserFiche() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php');
        }

        $idVisiteur = intval($_POST['id_visiteur'] ?? 0);
        $mois       = $_POST['mois'] ?? '';

        if ($idVisiteur && preg_match('/^\d{6}$/', $mois)) {
            $this->ficheFraisModel->refuserFiche($idVisiteur, $mois);
        }

        redirect('index.php?success=1');
    }

    public function validerFraisHorsForfait() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php');
        }

        $id          = intval($_POST['id'] ?? 0);
        $commentaire = trim($_POST['commentaire'] ?? '');
        $idVisiteur  = intval($_POST['id_visiteur'] ?? 0);
        $mois        = $_POST['mois'] ?? '';

        if ($id > 0) {
            $this->fraisHorsForfaitModel->valider($id, $commentaire ?: null);
        }

        redirect('index.php?action=voir_fiche&visiteur=' . $idVisiteur . '&mois=' . $mois . '&success=1');
    }

    public function refuserFraisHorsForfait() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php');
        }

        $id          = intval($_POST['id'] ?? 0);
        $commentaire = trim($_POST['commentaire'] ?? '');
        $idVisiteur  = intval($_POST['id_visiteur'] ?? 0);
        $mois        = $_POST['mois'] ?? '';

        if (empty($commentaire)) {
            redirect('index.php?action=voir_fiche&visiteur=' . $idVisiteur . '&mois=' . $mois . '&error=commentaire_requis');
        }

        if ($id > 0) {
            $this->fraisHorsForfaitModel->refuser($id, $commentaire);
        }

        redirect('index.php?action=voir_fiche&visiteur=' . $idVisiteur . '&mois=' . $mois . '&success=1');
    }
}
