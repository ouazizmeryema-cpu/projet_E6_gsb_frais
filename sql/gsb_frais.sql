-- =======================================================================
-- Base de données GSB Frais
-- =======================================================================

CREATE DATABASE IF NOT EXISTS gsb_frais
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE gsb_frais;

-- Désactiver temporairement les contraintes FK pour permettre le DROP sans ordre précis
SET FOREIGN_KEY_CHECKS = 0;

-- Suppression des tables dans l'ordre inverse des dépendances
-- (utile pour réimporter proprement le script)
DROP TABLE IF EXISTS lignes_frais_hors_forfait;
DROP TABLE IF EXISTS lignes_frais_forfait;
DROP TABLE IF EXISTS fiches_frais;
DROP TABLE IF EXISTS frais_forfait;
DROP TABLE IF EXISTS utilisateurs;
DROP TABLE IF EXISTS etats_fiche;

SET FOREIGN_KEY_CHECKS = 1;

-- =======================================================================
-- Création des tables
-- Ordre respecté : tables sans FK d'abord, puis celles qui en dépendent
-- =======================================================================

-- 1. Table des états de fiche
--    (référencée par fiches_frais → doit exister en premier)
CREATE TABLE etats_fiche (
    id      INT         AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Table des utilisateurs
--    (référencée par fiches_frais, lignes_frais_forfait, lignes_frais_hors_forfait)
CREATE TABLE utilisateurs (
    id               INT          AUTO_INCREMENT PRIMARY KEY,
    nom              VARCHAR(50)  NOT NULL,
    prenom           VARCHAR(50)  NOT NULL,
    login            VARCHAR(50)  NOT NULL,
    mdp              VARCHAR(255) NOT NULL,
    type_utilisateur ENUM('visiteur', 'comptable', 'admin') NOT NULL,
    date_creation    DATETIME     DEFAULT CURRENT_TIMESTAMP,
    actif            BOOLEAN      DEFAULT TRUE,
    UNIQUE KEY uk_login (login),
    INDEX idx_type   (type_utilisateur)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Table des frais forfaitaires
--    (référencée par lignes_frais_forfait)
CREATE TABLE frais_forfait (
    id      INT           AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50)   NOT NULL,
    montant DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Table des fiches de frais
--    (dépend de : utilisateurs, etats_fiche)
CREATE TABLE fiches_frais (
    id               INT           AUTO_INCREMENT PRIMARY KEY,
    id_visiteur      INT           NOT NULL,
    mois             VARCHAR(6)    NOT NULL,        -- Format : YYYYMM
    nb_justificatifs INT           DEFAULT 0,
    montant_valide   DECIMAL(10,2) DEFAULT 0.00,
    date_modif       DATETIME      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_etat          INT           NOT NULL DEFAULT 1,
    date_cloture     DATETIME      NULL,
    UNIQUE KEY uk_visiteur_mois (id_visiteur, mois),
    INDEX idx_mois (mois),
    INDEX idx_etat (id_etat),
    CONSTRAINT fk_fiches_visiteur FOREIGN KEY (id_visiteur)
        REFERENCES utilisateurs(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_fiches_etat FOREIGN KEY (id_etat)
        REFERENCES etats_fiche(id)  ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Table des lignes de frais forfaitaires
--    (dépend de : utilisateurs, frais_forfait)
CREATE TABLE lignes_frais_forfait (
    id               INT        AUTO_INCREMENT PRIMARY KEY,
    id_visiteur      INT        NOT NULL,
    id_frais_forfait INT        NOT NULL,
    mois             VARCHAR(6) NOT NULL,
    quantite         INT        DEFAULT 0,
    UNIQUE KEY uk_visiteur_frais_mois (id_visiteur, id_frais_forfait, mois),
    INDEX idx_mois (mois),
    CONSTRAINT fk_lff_visiteur FOREIGN KEY (id_visiteur)
        REFERENCES utilisateurs(id)  ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_lff_frais FOREIGN KEY (id_frais_forfait)
        REFERENCES frais_forfait(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Table des lignes de frais hors forfait
--    (dépend de : utilisateurs)
CREATE TABLE lignes_frais_hors_forfait (
    id                    INT           AUTO_INCREMENT PRIMARY KEY,
    id_visiteur           INT           NOT NULL,
    mois                  VARCHAR(6)    NOT NULL,
    date_frais            DATE          NOT NULL,
    libelle               VARCHAR(200)  NOT NULL,
    montant               DECIMAL(10,2) NOT NULL,
    justificatif          VARCHAR(255)  NULL,
    valide                BOOLEAN       DEFAULT NULL, -- NULL=en attente, TRUE=accepté, FALSE=refusé
    commentaire_comptable TEXT          NULL,
    date_validation       DATETIME      NULL,
    INDEX idx_mois   (mois),
    INDEX idx_valide (valide),
    CONSTRAINT fk_lhf_visiteur FOREIGN KEY (id_visiteur)
        REFERENCES utilisateurs(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================================
-- Données initiales
-- =======================================================================

-- États de fiche
-- IMPORTANT : l'ordre des INSERT fixe les id (utilisés dans le code PHP)
--   1 = Saisie en cours   (fiche créée, modifiable par le visiteur)
--   2 = Clôturée          (visiteur a clôturé, en attente comptable)
--   3 = En attente        (réservé pour usage futur)
--   4 = Validée           (comptable a validé)
--   5 = Refusée           (comptable a refusé)
--   6 = Payée             (remboursement effectué)
INSERT INTO etats_fiche (libelle) VALUES
    ('Saisie en cours'),
    ('Clôturée'),
    ('En attente de validation'),
    ('Validée'),
    ('Refusée'),
    ('Payée');

-- Frais forfaitaires de référence
INSERT INTO frais_forfait (libelle, montant) VALUES
    ('Nuitée hôtelière', 80.00),
    ('Repas restaurant',  25.00),
    ('Kilométrage',        0.62),
    ('Frais de péage',    15.00);

-- Utilisateurs de test
-- Les mots de passe sont hachés avec password_hash($pwd, PASSWORD_DEFAULT)
-- Pour regénérer les hachés, exécuter : php sql/generate_passwords.php

-- admin / admin123
INSERT INTO utilisateurs (nom, prenom, login, mdp, type_utilisateur) VALUES
    ('Administrateur', 'GSB', 'admin',
     '$2y$12$TjR9GmAoOQmT6q3Juk1j.uyIZUd05jtJMlG75rLKgTjnaDK4TjfeO',
     'admin');

-- visiteur1 / visiteur123
INSERT INTO utilisateurs (nom, prenom, login, mdp, type_utilisateur) VALUES
    ('Martin', 'Jean', 'visiteur1',
     '$2y$12$cigC8pJ0pz0gjc5mrrfYNebsY6baHONsmAEabX5tUvcNiHRN0G4wS',
     'visiteur');

-- comptable1 / comptable123
INSERT INTO utilisateurs (nom, prenom, login, mdp, type_utilisateur) VALUES
    ('Dupont', 'Marie', 'comptable1',
     '$2y$12$eq8riJkHbICIKGW6hksSheQK1eohHK6eUyzLii3iLh6Q6kLpGqHEq',
     'comptable');
