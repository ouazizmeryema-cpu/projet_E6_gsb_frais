<?php
require_once __DIR__ . '/../models/FicheFrais.php';
require_once __DIR__ . '/../models/FraisForfait.php';
require_once __DIR__ . '/../models/FraisHorsForfait.php';

class VisiteurController {
    private $ficheFraisModel;
    private $fraisForfaitModel;
    private $fraisHorsForfaitModel;

    public function __construct() {
        checkUserType('visiteur');
        $this->ficheFraisModel       = new FicheFrais();
        $this->fraisForfaitModel     = new FraisForfait();
        $this->fraisHorsForfaitModel = new FraisHorsForfait();
    }

    public function dashboard() {
        $idVisiteur = $_SESSION['user_id'];
        $moisActuel = date('Ym');
        $moisSelectionne = $_GET['mois'] ?? $moisActuel;

        // Valider le format et ne pas autoriser le futur
        if (!preg_match('/^\d{6}$/', $moisSelectionne) || $moisSelectionne > $moisActuel) {
            $moisSelectionne = $moisActuel;
        }

        $this->ficheFraisModel->createFiche($idVisiteur, $moisActuel);

        $fiche            = $this->ficheFraisModel->getFicheByMois($idVisiteur, $moisSelectionne);
        $fraisForfaits    = $this->fraisForfaitModel->getAll();
        $lignesForfait    = $this->fraisForfaitModel->getLignesByMois($idVisiteur, $moisSelectionne);
        $fraisHorsForfait = $this->fraisHorsForfaitModel->getByMois($idVisiteur, $moisSelectionne);
        $historique       = $this->ficheFraisModel->getFichesByVisiteur($idVisiteur);
        $mois             = $moisSelectionne;

        require_once __DIR__ . '/../views/visiteur/dashboard.php';
    }

    public function saveFraisForfait() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php');
        }

        $idVisiteur    = $_SESSION['user_id'];
        $mois          = $_POST['mois'] ?? date('Ym');
        $fraisForfaits = $this->fraisForfaitModel->getAll();

        foreach ($fraisForfaits as $frais) {
            $quantite = max(0, intval($_POST['quantite_' . $frais['id']] ?? 0));
            $this->fraisForfaitModel->saveLigne($idVisiteur, $frais['id'], $mois, $quantite);
        }

        redirect('index.php?success=1');
    }

    public function addFraisHorsForfait() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php');
        }

        $idVisiteur = $_SESSION['user_id'];
        $mois       = $_POST['mois']       ?? date('Ym');
        $dateFrais  = $_POST['date_frais'] ?? '';
        $libelle    = trim($_POST['libelle'] ?? '');
        $montant    = floatval($_POST['montant'] ?? 0);

        if (empty($dateFrais) || empty($libelle) || $montant <= 0) {
            redirect('index.php?error=champs_invalides');
        }

        $justificatif = null;
        if (isset($_FILES['justificatif']) && $_FILES['justificatif']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = UPLOAD_PATH . '/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext        = strtolower(pathinfo($_FILES['justificatif']['name'], PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
            if (in_array($ext, $allowedExt)) {
                $fileName   = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['justificatif']['name']));
                $targetFile = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['justificatif']['tmp_name'], $targetFile)) {
                    $justificatif = $fileName;
                }
            }
        }

        $this->fraisHorsForfaitModel->create($idVisiteur, $mois, $dateFrais, $libelle, $montant, $justificatif);

        redirect('index.php?success=1');
    }

    public function deleteFraisHorsForfait() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php');
        }

        $id         = intval($_POST['id'] ?? 0);
        $idVisiteur = $_SESSION['user_id'];

        if ($id > 0) {
            $this->fraisHorsForfaitModel->delete($id, $idVisiteur);
        }

        redirect('index.php?success=1');
    }

    public function creerFiche() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php');
        }

        $idVisiteur = $_SESSION['user_id'];
        $mois       = $_POST['mois'] ?? '';

        // Valider le format AAAAMM
        if (!preg_match('/^\d{6}$/', $mois)) {
            redirect('index.php?error=mois_invalide');
        }

        // Ne pas permettre de créer une fiche dans le futur
        if ($mois > date('Ym')) {
            redirect('index.php?error=mois_futur');
        }

        $this->ficheFraisModel->createFiche($idVisiteur, $mois);

        redirect('index.php?mois=' . $mois . '&success=1');
    }

    public function cloturerMois() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php');
        }

        $idVisiteur = $_SESSION['user_id'];
        $mois       = $_POST['mois'] ?? date('Ym');

        $this->ficheFraisModel->cloturerFiche($idVisiteur, $mois);

        redirect('index.php?success=1');
    }

    public function graphique() {
        $idVisiteur = $_SESSION['user_id'];
        $données    = $this->ficheFraisModel->getFraisParMois($idVisiteur);

        $mois   = [];
        $totaux = [];

        $moisFr = [
            '01' => 'Janvier', '02' => 'Février',  '03' => 'Mars',
            '04' => 'Avril',   '05' => 'Mai',       '06' => 'Juin',
            '07' => 'Juillet', '08' => 'Août',      '09' => 'Septembre',
            '10' => 'Octobre', '11' => 'Novembre',  '12' => 'Décembre'
        ];

        foreach ($données as $ligne) {
            $annee    = substr($ligne['mois'], 0, 4);
            $num      = substr($ligne['mois'], 4, 2);
            $mois[]   = ($moisFr[$num] ?? $num) . ' ' . $annee;
            $totaux[] = (float) $ligne['montant_valide'];
        }

        $moisJson   = json_encode($mois);
        $totauxJson = json_encode($totaux);

        require_once __DIR__ . '/../views/visiteur/graphique.php';
    }
}