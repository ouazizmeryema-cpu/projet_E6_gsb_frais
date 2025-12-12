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
}

