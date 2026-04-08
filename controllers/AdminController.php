<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/FraisForfait.php';


class AdminController {
    private $userModel;
    private $forfaitModel;
    private $db;

    public function __construct() {
        checkUserType('admin');
        $this->userModel   = new User();
        $this->forfaitModel = new FraisForfait();
        $this->db          = Database::getInstance()->getConnection();
    }

    public function dashboard() {
        $users   = $this->userModel->getAll();
        $forfaits = $this->forfaitModel->getAll();
        $stats   = $this->getStats();
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }

    private function getStats() {
        $stats = ['users' => [], 'nb_fiches' => 0, 'nb_fiches_cloturees' => 0];

        $stmt = $this->db->query("SELECT type_utilisateur, COUNT(*) as nb FROM utilisateurs WHERE actif = 1 GROUP BY type_utilisateur");
        foreach ($stmt->fetchAll() as $row) {
            $stats['users'][$row['type_utilisateur']] = $row['nb'];
        }

        $stmt = $this->db->query("SELECT COUNT(*) as nb FROM fiches_frais");
        $stats['nb_fiches'] = $stmt->fetch()['nb'];

        $stmt = $this->db->query("SELECT COUNT(*) as nb FROM fiches_frais WHERE id_etat = 2");
        $stats['nb_fiches_cloturees'] = $stmt->fetch()['nb'];

        return $stats;
    }

    // -------------------------------------------------------------------------
    // Utilisateurs
    // -------------------------------------------------------------------------

    public function addUser() {
        $nom    = trim($_POST['nom']    ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $login  = trim($_POST['login']  ?? '');
        $mdp    = $_POST['mdp']         ?? '';
        $type   = $_POST['type_utilisateur'] ?? '';

        if (!$nom || !$prenom || !$login || !$mdp || !in_array($type, ['visiteur', 'comptable', 'admin'])) {
            $_SESSION['flash_error'] = 'Tous les champs sont obligatoires.';
            redirect('index.php');
        }

        if ($this->userModel->loginExists($login)) {
            $_SESSION['flash_error'] = "Le login « " . htmlspecialchars($login) . " » est déjà utilisé.";
            redirect('index.php');
        }

        $this->userModel->create($nom, $prenom, $login, $mdp, $type);
        $_SESSION['flash_success'] = "Utilisateur $prenom $nom créé avec succès. Son compte est inactif par défaut — activez-le dans la liste.";
        redirect('index.php');
    }

    public function editUser() {
        $id     = (int)($_POST['id']    ?? 0);
        $nom    = trim($_POST['nom']    ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $login  = trim($_POST['login']  ?? '');
        $type   = $_POST['type_utilisateur'] ?? '';
        $mdp    = $_POST['mdp'] ?? '';

        if (!$id || !$nom || !$prenom || !$login || !in_array($type, ['visiteur', 'comptable', 'admin'])) {
            $_SESSION['flash_error'] = 'Données invalides.';
            redirect('index.php');
        }

        if ($this->userModel->loginExists($login, $id)) {
            $_SESSION['flash_error'] = "Le login « " . htmlspecialchars($login) . " » est déjà utilisé.";
            redirect('index.php');
        }

        $this->userModel->update($id, $nom, $prenom, $login, $type);
        if ($mdp) {
            $this->userModel->updatePassword($id, $mdp);
        }
        $_SESSION['flash_success'] = "Utilisateur modifié avec succès.";
        redirect('index.php');
    }

    // -------------------------------------------------------------------------
    // Activation / Désactivation du compte
    // toggleUser() : récupère l'id depuis $_POST, vérifie, puis appelle le modèle
    // toggleActif() est dans User.php (modèle), pas ici
    // -------------------------------------------------------------------------

    public function toggleUser() {
        $id = (int)($_POST['id'] ?? 0);

        // Si l'id est absent ou invalide, on arrête
        if (!$id) {
            $_SESSION['flash_error'] = 'Identifiant invalide.';
            redirect('index.php');
        }

        // L'admin ne peut pas désactiver son propre compte
        if ($id === (int)$_SESSION['user_id']) {
            $_SESSION['flash_error'] = 'Vous ne pouvez pas modifier votre propre compte.';
            redirect('index.php');
        }

        // On appelle le modèle pour inverser l'état actif
        $this->userModel->toggleActif($id);
        $_SESSION['flash_success'] = "Statut de l'utilisateur modifié.";
        redirect('index.php');
    }

    // -------------------------------------------------------------------------
    // Frais forfaitaires
    // -------------------------------------------------------------------------

    public function addForfait() {
        $libelle = trim($_POST['libelle'] ?? '');
        $montant = str_replace(',', '.', $_POST['montant'] ?? '0');
        $montant = (float)$montant;

        if (!$libelle || $montant <= 0) {
            $_SESSION['flash_error'] = 'Libellé et montant valide requis.';
            redirect('index.php');
        }

        $this->forfaitModel->create($libelle, $montant);
        $_SESSION['flash_success'] = "Frais forfaitaire « $libelle » ajouté.";
        redirect('index.php');
    }

    public function editForfait() {
        $id      = (int)($_POST['id'] ?? 0);
        $libelle = trim($_POST['libelle'] ?? '');
        $montant = str_replace(',', '.', $_POST['montant'] ?? '0');
        $montant = (float)$montant;

        if (!$id || !$libelle || $montant <= 0) {
            $_SESSION['flash_error'] = 'Données invalides.';
            redirect('index.php');
        }

        $this->forfaitModel->update($id, $libelle, $montant);
        $_SESSION['flash_success'] = "Frais forfaitaire modifié.";
        redirect('index.php');
    }

    public function deleteForfait() {
        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            $_SESSION['flash_error'] = 'Identifiant invalide.';
            redirect('index.php');
        }

        $this->forfaitModel->delete($id);
        $_SESSION['flash_success'] = "Frais forfaitaire supprimé.";
        redirect('index.php');
    }
}