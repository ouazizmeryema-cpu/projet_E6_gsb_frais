# Explication du code ajouté — Dashboard Admin

---

## 1. `models/User.php`

### `getAll()`
```php
public function getAll() {
    $stmt = $this->db->query("SELECT id, nom, prenom, login, type_utilisateur, actif, date_creation FROM utilisateurs ORDER BY type_utilisateur, nom, prenom");
    return $stmt->fetchAll();
}
```
**Ce que ça fait :**
Récupère TOUS les utilisateurs de la base (actifs ET inactifs, tous rôles confondus).
On trie par rôle puis par nom pour que le tableau soit lisible.
`fetchAll()` retourne un tableau PHP avec tous les résultats.

---

### `loginExists($login, $excludeId = null)`
```php
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
```
**Ce que ça fait :**
Vérifie si un login est déjà pris dans la base.
- Sans `$excludeId` — utilisé à la **création** : on cherche si le login existe quelque part.
- Avec `$excludeId` — utilisé à la **modification** : on exclut l'utilisateur lui-même pour ne pas bloquer s'il garde son propre login.
- Retourne `true` si le login est déjà pris, `false` sinon.

---

### `create($nom, $prenom, $login, $mdp, $type)`
```php
public function create($nom, $prenom, $login, $mdp, $type) {
    $stmt = $this->db->prepare("INSERT INTO utilisateurs (nom, prenom, login, mdp, type_utilisateur) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$nom, $prenom, $login, password_hash($mdp, PASSWORD_DEFAULT), $type]);
}
```
**Ce que ça fait :**
Insère un nouvel utilisateur en base.
`password_hash($mdp, PASSWORD_DEFAULT)` hache le mot de passe avec bcrypt avant de le stocker — on ne stocke **jamais** un mot de passe en clair.
Les `?` sont des paramètres préparés : PDO les remplace proprement, ce qui protège contre les injections SQL.

---

### `update($id, $nom, $prenom, $login, $type)`
```php
public function update($id, $nom, $prenom, $login, $type) {
    $stmt = $this->db->prepare("UPDATE utilisateurs SET nom = ?, prenom = ?, login = ?, type_utilisateur = ? WHERE id = ?");
    return $stmt->execute([$nom, $prenom, $login, $type, $id]);
}
```
**Ce que ça fait :**
Met à jour les informations d'un utilisateur existant (sans toucher au mot de passe).
Le mot de passe est géré séparément dans `updatePassword()` pour pouvoir le laisser inchangé si le champ est vide dans le formulaire.

---

### `updatePassword($id, $mdp)`
```php
public function updatePassword($id, $mdp) {
    $stmt = $this->db->prepare("UPDATE utilisateurs SET mdp = ? WHERE id = ?");
    return $stmt->execute([password_hash($mdp, PASSWORD_DEFAULT), $id]);
}
```
**Ce que ça fait :**
Change uniquement le mot de passe d'un utilisateur.
Appelé dans le controller seulement si le champ mdp du formulaire n'est pas vide.

---

### `toggleActif($id)`
```php
public function toggleActif($id) {
    $stmt = $this->db->prepare("UPDATE utilisateurs SET actif = NOT actif WHERE id = ?");
    return $stmt->execute([$id]);
}
```
**Ce que ça fait :**
Bascule le champ `actif` entre 0 et 1 avec `NOT actif` directement en SQL.
Si l'utilisateur est actif → il devient inactif, et inversement.
Un utilisateur inactif ne peut plus se connecter (vérifié dans `authenticate()` avec `AND actif = 1`).

---

## 2. `models/FraisForfait.php` — méthodes ajoutées

### `getById($id)`
```php
public function getById($id) {
    $stmt = $this->db->prepare("SELECT * FROM frais_forfait WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}
```
**Ce que ça fait :**
Récupère un seul forfait par son id.
`fetch()` retourne une seule ligne (ou `false` si rien trouvé).

---

### `create($libelle, $montant)`
```php
public function create($libelle, $montant) {
    $stmt = $this->db->prepare("INSERT INTO frais_forfait (libelle, montant) VALUES (?, ?)");
    return $stmt->execute([$libelle, $montant]);
}
```
**Ce que ça fait :**
Insère un nouveau type de frais forfaitaire (ex : "Taxi", 20.00).

---

### `update($id, $libelle, $montant)`
```php
public function update($id, $libelle, $montant) {
    $stmt = $this->db->prepare("UPDATE frais_forfait SET libelle = ?, montant = ? WHERE id = ?");
    return $stmt->execute([$libelle, $montant, $id]);
}
```
**Ce que ça fait :**
Modifie le libellé et/ou le montant d'un forfait existant.

---

### `delete($id)`
```php
public function delete($id) {
    $stmt = $this->db->prepare("DELETE FROM frais_forfait WHERE id = ?");
    return $stmt->execute([$id]);
}
```
**Ce que ça fait :**
Supprime un forfait de la base.
Attention : si des lignes de frais existent pour ce forfait (table `lignes_frais_forfait`), elles seront aussi supprimées grâce à la contrainte `ON DELETE CASCADE` définie dans le SQL.

---

## 3. `controllers/AdminController.php` — complet

### Le constructeur
```php
public function __construct() {
    checkUserType('admin');
    $this->userModel    = new User();
    $this->forfaitModel = new FraisForfait();
    $this->db           = Database::getInstance()->getConnection();
}
```
**Ce que ça fait :**
`checkUserType('admin')` vérifie dès l'instanciation que l'utilisateur connecté est bien un admin. S'il ne l'est pas, il est redirigé automatiquement. C'est la première ligne de défense.
Ensuite on instancie les deux modèles dont on a besoin.

---

### `dashboard()`
```php
public function dashboard() {
    $users    = $this->userModel->getAll();
    $forfaits = $this->forfaitModel->getAll();
    $stats    = $this->getStats();
    require_once __DIR__ . '/../views/admin/dashboard.php';
}
```
**Ce que ça fait :**
Charge toutes les données nécessaires à la vue, puis inclut la vue.
Les variables `$users`, `$forfaits` et `$stats` seront disponibles directement dans le fichier de vue.

---

### `getStats()` (méthode privée)
```php
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
```
**Ce que ça fait :**
Fait 3 requêtes SQL pour construire un tableau de statistiques :
1. `GROUP BY type_utilisateur` → compte les actifs par rôle → `$stats['users']['visiteur']`, `$stats['users']['comptable']`, etc.
2. Compte toutes les fiches de frais.
3. Compte les fiches avec `id_etat = 2` (état "Clôturée").
Méthode `private` car elle n'est utilisée qu'en interne par `dashboard()`.

---

### `addUser()` et `editUser()`
```php
// Exemple sur addUser
$nom  = trim($_POST['nom'] ?? '');
// ...
if (!$nom || !$prenom || !$login || !$mdp || !in_array($type, ['visiteur', 'comptable', 'admin'])) {
    $_SESSION['flash_error'] = 'Tous les champs sont obligatoires.';
    redirect('index.php');
}
if ($this->userModel->loginExists($login)) {
    $_SESSION['flash_error'] = "Le login est déjà utilisé.";
    redirect('index.php');
}
$this->userModel->create($nom, $prenom, $login, $mdp, $type);
$_SESSION['flash_success'] = "Utilisateur créé.";
redirect('index.php');
```
**Ce que ça fait — étape par étape :**
1. `trim()` supprime les espaces autour des valeurs du formulaire.
2. `?? ''` évite une erreur PHP si la clé n'existe pas dans `$_POST`.
3. `in_array($type, [...])` valide que le rôle envoyé est bien une valeur autorisée (sécurité).
4. Si validation échoue → message d'erreur en session + redirection.
5. Si login déjà pris → idem.
6. Sinon → création + message de succès + redirection.

Le pattern **redirection après POST** (PRG) est important : si l'utilisateur rafraîchit la page après une action, le formulaire n'est pas renvoyé une deuxième fois.

Pour `editUser()` : même logique, mais on passe `$id` à `loginExists()` pour exclure l'utilisateur lui-même, et on appelle `updatePassword()` seulement si le champ mdp est non vide.

---

### `toggleUser()`
```php
public function toggleUser() {
    $id = (int)($_POST['id'] ?? 0);

    if ($id === (int)$_SESSION['user_id']) {
        $_SESSION['flash_error'] = 'Vous ne pouvez pas désactiver votre propre compte.';
        redirect('index.php');
    }

    $this->userModel->toggleActif($id);
    redirect('index.php');
}
```
**Ce que ça fait :**
`(int)` force la conversion en entier — si quelqu'un envoie du texte malveillant à la place d'un id, ça donne `0` et la suite échoue proprement.
La vérification `$id === (int)$_SESSION['user_id']` empêche un admin de se bloquer lui-même accidentellement.

---

### `addForfait()` / `editForfait()` / `deleteForfait()`
Même structure que pour les utilisateurs : validation → action modèle → flash → redirect.

```php
$montant = str_replace(',', '.', $_POST['montant'] ?? '0');
$montant = (float)$montant;
```
Le `str_replace` gère le cas où l'utilisateur écrit `20,50` avec une virgule au lieu d'un point — on convertit avant de caster en float.

---

## 4. `index.php` — routing admin

```php
case 'admin':
    require_once 'controllers/AdminController.php';
    $controller = new AdminController();
    switch ($action) {
        case 'add_user':    $controller->addUser();    break;
        case 'edit_user':   $controller->editUser();   break;
        case 'toggle_user': $controller->toggleUser(); break;
        case 'add_forfait':    $controller->addForfait();    break;
        case 'edit_forfait':   $controller->editForfait();   break;
        case 'delete_forfait': $controller->deleteForfait(); break;
        default:            $controller->dashboard();
    }
    break;
```
**Ce que ça fait :**
Le paramètre `?action=` dans l'URL détermine quelle méthode du controller est appelée.
Par défaut (si pas d'action ou action inconnue), on affiche le dashboard.
Ce fichier est le seul point d'entrée de l'application (front controller).

---

## 5. `views/admin/dashboard.php` — points clés

### Affichage des stats
```php
<div class="stat-value"><?php echo $stats['users']['visiteur'] ?? 0; ?></div>
```
`?? 0` : si aucun visiteur actif n'existe, la clé n'est pas dans le tableau → on affiche 0 plutôt qu'une erreur.

---

### Passer les données à la modale via JS
```php
<button onclick="openEditUser(<?php echo htmlspecialchars(json_encode($u)); ?>)">
```
`json_encode($u)` convertit le tableau PHP de l'utilisateur en objet JSON.
`htmlspecialchars()` sécurise les caractères spéciaux pour éviter une faille XSS si un nom contient des guillemets ou du HTML.
Côté JS, `openEditUser(u)` reçoit l'objet et remplit les champs de la modale :
```js
function openEditUser(u) {
    document.getElementById('edit-user-id').value     = u.id;
    document.getElementById('edit-user-nom').value    = u.nom;
    document.getElementById('edit-user-prenom').value = u.prenom;
    document.getElementById('edit-user-login').value  = u.login;
    document.getElementById('edit-user-type').value   = u.type_utilisateur;
    openModal('modal-edit-user');
}
```

---

### Fermeture de modale en cliquant en dehors
```js
window.onclick = function(e) {
    document.querySelectorAll('.modal').forEach(function(m) {
        if (e.target === m) m.style.display = 'none';
    });
};
```
`e.target === m` : on vérifie que le clic est sur le fond sombre (l'overlay), pas sur le contenu de la modale. Si oui, on ferme.

---

### Messages flash
```php
<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success">
        <?php echo htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
    </div>
<?php endif; ?>
```
Le message est affiché **puis immédiatement supprimé** de la session avec `unset()`.
Ainsi il ne réapparaît pas si l'utilisateur rafraîchit la page.