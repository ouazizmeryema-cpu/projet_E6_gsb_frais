-- Base de données GSB Frais
-- Création de la base de données
CREATE DATABASE IF NOT EXISTS gsb_frais CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gsb_frais;

-- Table des utilisateurs (visiteurs, comptables, admin)
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    login VARCHAR(50) UNIQUE NOT NULL,
    mdp VARCHAR(255) NOT NULL,
    type_utilisateur ENUM('visiteur', 'comptable', 'admin') NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    actif BOOLEAN DEFAULT TRUE,
    INDEX idx_login (login),
    INDEX idx_type (type_utilisateur)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des fiches de frais (mois)
CREATE TABLE IF NOT EXISTS fiches_frais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_visiteur INT NOT NULL,
    mois VARCHAR(6) NOT NULL, -- Format: YYYYMM
    nb_justificatifs INT DEFAULT 0,
    montant_valide DECIMAL(10,2) DEFAULT 0.00,
    date_modif DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_etat INT NOT NULL DEFAULT 1,
    date_cloture DATETIME NULL,
    UNIQUE KEY unique_visiteur_mois (id_visiteur, mois),
    FOREIGN KEY (id_visiteur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_mois (mois),
    INDEX idx_etat (id_etat)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des états de fiche
CREATE TABLE IF NOT EXISTS etats_fiche (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des frais forfaitaires
CREATE TABLE IF NOT EXISTS frais_forfait (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL,
    montant DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des lignes de frais forfaitaires
CREATE TABLE IF NOT EXISTS lignes_frais_forfait (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_visiteur INT NOT NULL,
    id_frais_forfait INT NOT NULL,
    mois VARCHAR(6) NOT NULL,
    quantite INT DEFAULT 0,
    FOREIGN KEY (id_visiteur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_frais_forfait) REFERENCES frais_forfait(id) ON DELETE CASCADE,
    UNIQUE KEY unique_visiteur_frais_mois (id_visiteur, id_frais_forfait, mois),
    INDEX idx_mois (mois)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des frais hors forfait
CREATE TABLE IF NOT EXISTS lignes_frais_hors_forfait (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_visiteur INT NOT NULL,
    mois VARCHAR(6) NOT NULL,
    date_frais DATE NOT NULL,
    libelle VARCHAR(200) NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    justificatif VARCHAR(255) NULL,
    valide BOOLEAN DEFAULT NULL, -- NULL = en attente, TRUE = accepté, FALSE = refusé
    commentaire_comptable TEXT NULL,
    date_validation DATETIME NULL,
    FOREIGN KEY (id_visiteur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_mois (mois),
    INDEX idx_valide (valide)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des états
INSERT INTO etats_fiche (libelle) VALUES
('Saisie en cours'),
('Clôturée'),
('En attente de validation'),
('Validée'),
('Refusée'),
('Payée');

-- Insertion des frais forfaitaires
INSERT INTO frais_forfait (libelle, montant) VALUES
('Nuitée hôtelière', 80.00),
('Repas restaurant', 25.00),
('Kilométrage', 0.62),
('Frais de péage', 15.00);

-- Insertion d'un utilisateur admin par défaut
-- Login: admin / Mot de passe: admin123
INSERT INTO utilisateurs (nom, prenom, login, mdp, type_utilisateur) VALUES
('Administrateur', 'GSB', 'admin', '$2y$12$TjR9GmAoOQmT6q3Juk1j.uyIZUd05jtJMlG75rLKgTjnaDK4TjfeO', 'admin');

-- Insertion d'un visiteur de test
-- Login: visiteur1 / Mot de passe: visiteur123
INSERT INTO utilisateurs (nom, prenom, login, mdp, type_utilisateur) VALUES
('Martin', 'Jean', 'visiteur1', '$2y$12$cigC8pJ0pz0gjc5mrrfYNebsY6baHONsmAEabX5tUvcNiHRN0G4wS', 'visiteur');

-- Insertion d'un comptable de test
-- Login: comptable1 / Mot de passe: comptable123
INSERT INTO utilisateurs (nom, prenom, login, mdp, type_utilisateur) VALUES
('Dupont', 'Marie', 'comptable1', '$2y$12$eq8riJkHbICIKGW6hksSheQK1eohHK6eUyzLii3iLh6Q6kLpGqHEq', 'comptable');

