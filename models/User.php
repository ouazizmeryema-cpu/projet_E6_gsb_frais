<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function authenticate($login, $password) {
        $stmt = $this->db->prepare("SELECT id, nom, prenom, login, mdp, type_utilisateur FROM utilisateurs WHERE login = ? AND actif = 1");
        $stmt->execute([$login]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['mdp'])) {
            unset($user['mdp']); // Ne pas retourner le mot de passe
            return $user;
        }
        
        return false;
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT id, nom, prenom, login, type_utilisateur FROM utilisateurs WHERE id = ? AND actif = 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getAllVisiteurs() {
        $stmt = $this->db->query("SELECT id, nom, prenom, login FROM utilisateurs WHERE type_utilisateur = 'visiteur' AND actif = 1 ORDER BY nom, prenom");
        return $stmt->fetchAll();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT id, nom, prenom, login, type_utilisateur, actif, date_creation FROM utilisateurs ORDER BY type_utilisateur, nom, prenom");
        return $stmt->fetchAll();
    }

    public function loginExists($login, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT id FROM utilisateurs WHERE login = ? AND id != ?");
            $stmt->execute([$login, $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM utilisateurs WHERE login = ?");
            $stmt->execute([$login]);
        }
        return $stmt->fetch() !== false;
    }

    public function create($nom, $prenom, $login, $mdp, $type) {
        $stmt = $this->db->prepare("INSERT INTO utilisateurs (nom, prenom, login, mdp, type_utilisateur, actif) VALUES (?, ?, ?, ?, ?, 0)");
        return $stmt->execute([$nom, $prenom, $login, password_hash($mdp, PASSWORD_DEFAULT), $type]);
    }

    public function update($id, $nom, $prenom, $login, $type) {
        $stmt = $this->db->prepare("UPDATE utilisateurs SET nom = ?, prenom = ?, login = ?, type_utilisateur = ? WHERE id = ?");
        return $stmt->execute([$nom, $prenom, $login, $type, $id]);
    }

    public function updatePassword($id, $mdp) {
        $stmt = $this->db->prepare("UPDATE utilisateurs SET mdp = ? WHERE id = ?");
        return $stmt->execute([password_hash($mdp, PASSWORD_DEFAULT), $id]);
    }

    public function toggleActif($id) {
        $stmt = $this->db->prepare("UPDATE utilisateurs SET actif = NOT actif WHERE id = ?");
        return $stmt->execute([$id]);
    }


}

