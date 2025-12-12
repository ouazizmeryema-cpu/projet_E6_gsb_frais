<?php
require_once __DIR__ . '/../config/database.php';

class FicheFrais {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getFicheByMois($idVisiteur, $mois) {
        $stmt = $this->db->prepare("
            SELECT ff.*, ef.libelle as etat_libelle 
            FROM fiches_frais ff
            LEFT JOIN etats_fiche ef ON ff.id_etat = ef.id
            WHERE ff.id_visiteur = ? AND ff.mois = ?
        ");
        $stmt->execute([$idVisiteur, $mois]);
        return $stmt->fetch();
    }
    
    public function createFiche($idVisiteur, $mois) {
        $stmt = $this->db->prepare("INSERT INTO fiches_frais (id_visiteur, mois, id_etat) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE id_visiteur = id_visiteur");
        return $stmt->execute([$idVisiteur, $mois]);
    }
    
    public function cloturerFiche($idVisiteur, $mois) {
        $stmt = $this->db->prepare("UPDATE fiches_frais SET id_etat = 2, date_cloture = NOW() WHERE id_visiteur = ? AND mois = ?");
        return $stmt->execute([$idVisiteur, $mois]);
    }
    
    public function getFichesByVisiteur($idVisiteur) {
        $stmt = $this->db->prepare("
            SELECT ff.*, ef.libelle as etat_libelle 
            FROM fiches_frais ff
            LEFT JOIN etats_fiche ef ON ff.id_etat = ef.id
            WHERE ff.id_visiteur = ?
            ORDER BY ff.mois DESC
        ");
        $stmt->execute([$idVisiteur]);
        return $stmt->fetchAll();
    }
    
    public function getAllFichesEnAttente() {
        $stmt = $this->db->query("
            SELECT ff.*, ef.libelle as etat_libelle, u.nom, u.prenom
            FROM fiches_frais ff
            LEFT JOIN etats_fiche ef ON ff.id_etat = ef.id
            LEFT JOIN utilisateurs u ON ff.id_visiteur = u.id
            WHERE ff.id_etat = 2
            ORDER BY ff.date_cloture DESC
        ");
        return $stmt->fetchAll();
    }
    
    public function validerFiche($idVisiteur, $mois) {
        $stmt = $this->db->prepare("UPDATE fiches_frais SET id_etat = 4 WHERE id_visiteur = ? AND mois = ?");
        return $stmt->execute([$idVisiteur, $mois]);
    }
    
    public function refuserFiche($idVisiteur, $mois) {
        $stmt = $this->db->prepare("UPDATE fiches_frais SET id_etat = 5 WHERE id_visiteur = ? AND mois = ?");
        return $stmt->execute([$idVisiteur, $mois]);
    }
}

