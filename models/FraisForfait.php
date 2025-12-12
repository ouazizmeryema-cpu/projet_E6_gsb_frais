<?php
require_once __DIR__ . '/../config/database.php';

class FraisForfait {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM frais_forfait ORDER BY id");
        return $stmt->fetchAll();
    }
    
    public function getLignesByMois($idVisiteur, $mois) {
        $stmt = $this->db->prepare("
            SELECT lff.*, ff.libelle, ff.montant
            FROM lignes_frais_forfait lff
            LEFT JOIN frais_forfait ff ON lff.id_frais_forfait = ff.id
            WHERE lff.id_visiteur = ? AND lff.mois = ?
        ");
        $stmt->execute([$idVisiteur, $mois]);
        return $stmt->fetchAll();
    }
    
    public function saveLigne($idVisiteur, $idFraisForfait, $mois, $quantite) {
        $stmt = $this->db->prepare("
            INSERT INTO lignes_frais_forfait (id_visiteur, id_frais_forfait, mois, quantite)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE quantite = ?
        ");
        return $stmt->execute([$idVisiteur, $idFraisForfait, $mois, $quantite, $quantite]);
    }
}

