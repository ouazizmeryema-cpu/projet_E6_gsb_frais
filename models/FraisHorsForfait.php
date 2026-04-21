<?php
require_once __DIR__ . '/../config/database.php';

class FraisHorsForfait {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getByMois($idVisiteur, $mois) {
        $stmt = $this->db->prepare("
            SELECT * FROM lignes_frais_hors_forfait
            WHERE id_visiteur = ? AND mois = ?
            ORDER BY date_frais DESC
        ");
        $stmt->execute([$idVisiteur, $mois]);
        return $stmt->fetchAll();
    }
    
    public function create($idVisiteur, $mois, $dateFrais, $libelle, $montant, $justificatif = null) {
        $stmt = $this->db->prepare("
            INSERT INTO lignes_frais_hors_forfait (id_visiteur, mois, date_frais, libelle, montant, justificatif)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$idVisiteur, $mois, $dateFrais, $libelle, $montant, $justificatif]);
    }
    
    public function update($id, $dateFrais, $libelle, $montant) {
        $stmt = $this->db->prepare("
            UPDATE lignes_frais_hors_forfait
            SET date_frais = ?, libelle = ?, montant = ?
            WHERE id = ?
        ");
        return $stmt->execute([$dateFrais, $libelle, $montant, $id]);
    }
    
    public function delete($id, $idVisiteur) {
        $stmt = $this->db->prepare("DELETE FROM lignes_frais_hors_forfait WHERE id = ? AND id_visiteur = ?");
        return $stmt->execute([$id, $idVisiteur]);
    }
    
    public function valider($id, $commentaire = null) {
        $stmt = $this->db->prepare("
            UPDATE lignes_frais_hors_forfait
            SET valide = TRUE, commentaire_comptable = ?, date_validation = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$commentaire, $id]);
    }
    
    public function refuser($id, $commentaire) {
        $stmt = $this->db->prepare("
            UPDATE lignes_frais_hors_forfait
            SET valide = FALSE, commentaire_comptable = ?, date_validation = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$commentaire, $id]);
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM lignes_frais_hors_forfait WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}

