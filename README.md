# GSB Frais

Application de gestion des frais des visiteurs médicaux de la société Galaxy Swiss Bourdin.

---

## Stack technique

- **PHP** (MVC maison, sans framework)
- **MySQL** via PDO
- **HTML / CSS / JS** vanilla
- **WampServer** (développement local)

---

## Structure du projet

```
projet_E6_gsb_frais/
assets/
  css/style.css
  js/main.js
config/
│   ├── config.php       # Fonctions globales, session, helpers (url, redirect…)
│   └── database.php     # Connexion PDO (singleton)
├── controllers/
│   ├── AdminController.php
│   ├── AuthController.php
│   ├── ComptableController.php
│   └── VisiteurController.php
├── models/
│   ├── FicheFrais.php
│   ├── FraisForfait.php
│   ├── FraisHorsForfait.php
│   └── User.php
├── sql/
│   ├── gsb_frais.sql          # Script de création + données initiales
│   └── generate_passwords.php # Utilitaire pour regénérer les hachés bcrypt
├── views/
│   ├── admin/
│   │   └── dashboard.php
│   ├── auth/
│   │   └── login.php
│   ├── comptable/
│   │   ├── dashboard.php
│   │   └── voir_fiche.php
│   ├── visiteur/
│   │   └── dashboard.php
│   └── includes/
│       ├── header.php
│       └── footer.php
├── index.php    # Front controller (routing par rôle + action)
├── login.php
└── logout.php
```

---

## Installation

1. Importer `sql/gsb_frais.sql` dans MySQL (via phpMyAdmin ou CLI).
2. Configurer la connexion dans `config/database.php`.
3. Placer le projet dans `www/` (WampServer) et l'ouvrir dans le navigateur.

### Comptes de test

| Login      | Mot de passe | Rôle      |
|------------|--------------|-----------|
| admin      | admin123     | Admin     |
| visiteur1  | visiteur123  | Visiteur  |
| comptable1 | comptable123 | Comptable |

---

## Rôles et fonctionnalités

### Visiteur
- Saisie des frais forfaitaires du mois (nuitée, repas, kilométrage, péage)
- Ajout / suppression de frais hors forfait
- Clôture mensuelle de la fiche de frais

### Comptable
- Liste des fiches clôturées
- Visualisation détaillée d'une fiche
- Validation ou refus d'une fiche
- Validation ou refus des frais hors forfait individuels

### Admin
- Voir ci-dessous (section dédiée)

---

## Dashboard Admin — ce qui a été implémenté

### Statistiques (en haut de page)
Cartes affichant en temps réel :
- Nombre de visiteurs actifs
- Nombre de comptables actifs
- Nombre d'admins actifs
- Nombre total de fiches de frais
- Nombre de fiches clôturées (en attente de traitement)

---

### Gestion des utilisateurs

**Modèle `User.php` — méthodes ajoutées :**

| Méthode | Description |
|---|---|
| `getAll()` | Retourne tous les utilisateurs (tous rôles, actifs et inactifs) |
| `loginExists($login, $excludeId)` | Vérifie l'unicité d'un login (exclut un id pour l'édition) |
| `create($nom, $prenom, $login, $mdp, $type)` | Crée un utilisateur (mot de passe haché bcrypt) |
| `update($id, $nom, $prenom, $login, $type)` | Met à jour les infos d'un utilisateur |
| `updatePassword($id, $mdp)` | Met à jour le mot de passe (haché bcrypt) |
| `toggleActif($id)` | Bascule le statut actif/inactif |

**Controller `AdminController.php` — actions :**

| Action (GET `?action=`) | Méthode | Description |
|---|---|---|
| `add_user` | `addUser()` | Crée un utilisateur via POST |
| `edit_user` | `editUser()` | Modifie un utilisateur via POST (mdp optionnel) |
| `toggle_user` | `toggleUser()` | Active ou désactive un compte |

**Vue — tableau des utilisateurs :**
- Colonnes : Nom, Prénom, Login, Rôle (badge coloré), Statut, Actions
- Badge de rôle coloré : bleu (visiteur), vert (comptable), jaune (admin)
- Lignes grisées pour les comptes inactifs
- Bouton **Modifier** → ouvre une modale pré-remplie
- Bouton **Activer / Désactiver** avec confirmation
- Protection : impossible de désactiver son propre compte

---

### Gestion des frais forfaitaires

**Modèle `FraisForfait.php` — méthodes ajoutées :**

| Méthode | Description |
|---|---|
| `getById($id)` | Retourne un forfait par son id |
| `create($libelle, $montant)` | Crée un nouveau forfait |
| `update($id, $libelle, $montant)` | Modifie libellé et montant |
| `delete($id)` | Supprime un forfait |

**Controller `AdminController.php` — actions :**

| Action (GET `?action=`) | Méthode | Description |
|---|---|---|
| `add_forfait` | `addForfait()` | Crée un forfait via POST |
| `edit_forfait` | `editForfait()` | Modifie un forfait via POST |
| `delete_forfait` | `deleteForfait()` | Supprime un forfait (avec confirmation JS) |

**Vue — tableau des forfaits :**
- Colonnes : Libellé, Montant unitaire, Actions
- Bouton **Modifier** → modale pré-remplie
- Bouton **Supprimer** avec confirmation (dialog natif)

---

### Modales

Toutes les actions d'ajout et d'édition passent par des modales sans rechargement de page :

- Modale **Ajouter un utilisateur** (nom, prénom, login, mdp, rôle)
- Modale **Modifier un utilisateur** (mêmes champs, mdp optionnel)
- Modale **Ajouter un forfait** (libellé, montant)
- Modale **Modifier un forfait** (libellé, montant pré-rempli)

Fermeture via la croix, ou clic en dehors de la modale.

---

### Messages flash

Les actions POST redirigent toujours vers le dashboard (pattern PRG).
Les résultats sont communiqués via des messages flash stockés en session :
- `$_SESSION['flash_success']` → bandeau vert
- `$_SESSION['flash_error']` → bandeau rouge

---

### CSS ajouté (`assets/css/style.css`)

| Classe | Usage |
|---|---|
| `.stats-grid` | Grille responsive des cartes de stats |
| `.stat-card` | Carte individuelle (valeur + label) |
| `.stat-value` | Grand chiffre coloré en bleu primaire |
| `.section-header` | Flex row entre le titre de section et le bouton d'ajout |
| `.badge`, `.badge-visiteur`, `.badge-comptable`, `.badge-admin` | Badges colorés par rôle |
| `.row-inactive` | Opacité réduite pour les utilisateurs inactifs |

---

## Routing (index.php)

Le front controller dispatche les actions selon le rôle de l'utilisateur connecté :

```
GET/POST index.php?action=<action>
```

Les actions admin disponibles : `add_user`, `edit_user`, `toggle_user`, `add_forfait`, `edit_forfait`, `delete_forfait`. Par défaut : `dashboard`.

---

## Sécurité

- Mots de passe hachés avec `password_hash()` / vérifiés avec `password_verify()` (bcrypt)
- Vérification du rôle à l'instanciation de chaque controller (`checkUserType()`)
- Toutes les sorties HTML passent par `htmlspecialchars()`
- Requêtes SQL via PDO préparé (protection injection SQL)
- Validation côté serveur de tous les champs POST