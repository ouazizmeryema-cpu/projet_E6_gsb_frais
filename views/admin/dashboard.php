<?php
$pageTitle = 'Tableau de bord - Administrateur';
include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard">
    <h2>Panneau d'administration</h2>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['users']['visiteur'] ?? 0; ?></div>
            <div class="stat-label">Visiteur(s)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['users']['comptable'] ?? 0; ?></div>
            <div class="stat-label">Comptable(s)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['users']['admin'] ?? 0; ?></div>
            <div class="stat-label">Admin(s)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['nb_fiches']; ?></div>
            <div class="stat-label">Fiche(s) de frais</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['nb_fiches_cloturees']; ?></div>
            <div class="stat-label">Fiche(s) clôturée(s)</div>
        </div>
    </div>

    <!-- Utilisateurs -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Gestion des utilisateurs</h3>
            <button class="btn btn-primary btn-sm" onclick="openModal('modal-add-user')">+ Ajouter un utilisateur</button>
        </div>
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Login</th>
                        <th>Role</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr class="<?php echo $u['actif'] ? '' : 'row-inactive'; ?>">
                        <td><?php echo htmlspecialchars($u['nom']); ?></td>
                        <td><?php echo htmlspecialchars($u['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($u['login']); ?></td>
                        <td><span class="badge badge-<?php echo $u['type_utilisateur']; ?>"><?php echo ucfirst($u['type_utilisateur']); ?></span></td>
                        <td><?php echo $u['actif'] ? '<span class="text-success">Actif</span>' : '<span class="text-danger">Inactif</span>'; ?></td>
                        <td>
                            <button class="btn btn-secondary btn-sm"
                                onclick="openEditUser(<?php echo htmlspecialchars(json_encode($u)); ?>)">
                                Modifier
                            </button>
                            <! --   -- >
                            <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                            <form method="post" action="<?php echo url('index.php?action=toggle_user'); ?>" style="display:inline">
                                <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                                <button type="submit" class="btn btn-sm <?php echo $u['actif'] ? 'btn-warning' : 'btn-success'; ?>"
                                    onclick="return confirm('Confirmer le changement de statut ?')">
                                     <?php echo $u['actif'] ? 'Désactiver' : 'Activer' ; ?>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Frais forfaitaires -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Frais forfaitaires</h3>
            <button class="btn btn-primary btn-sm" onclick="openModal('modal-add-forfait')">+ Ajouter un forfait</button>
        </div>
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Libellé</th>
                        <th>Montant unitaire</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($forfaits as $f): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($f['libelle']); ?></td>
                        <td><?php echo number_format($f['montant'], 2, ',', ' '); ?> €</td>
                        <td>
                            <button class="btn btn-secondary btn-sm"
                                onclick="openEditForfait(<?php echo htmlspecialchars(json_encode($f)); ?>)">
                                Modifier
                            </button>
                            <form method="post" action="<?php echo url('index.php?action=delete_forfait'); ?>" style="display:inline">
                                <input type="hidden" name="id" value="<?php echo (int)$f['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Supprimer ce frais forfaitaire ?')">
                                    Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal : ajouter utilisateur -->
<div id="modal-add-user" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-add-user')">&times;</span>
        <h3>Ajouter un utilisateur</h3>
        <form method="post" action="<?php echo url('index.php?action=add_user'); ?>">
            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="nom" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Prénom</label>
                <input type="text" name="prenom" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Login</label>
                <input type="text" name="login" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="mdp" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Rôle</label>
                <select name="type_utilisateur" class="form-control" required>
                    <option value="visiteur">Visiteur</option>
                    <option value="comptable">Comptable</option>
                    <option value="admin">Administrateur</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Créer</button>
        </form>
    </div>
</div>

<!-- Modal : modifier utilisateur -->
<div id="modal-edit-user" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-edit-user')">&times;</span>
        <h3>Modifier l'utilisateur</h3>
        <form method="post" action="<?php echo url('index.php?action=edit_user'); ?>">
            <input type="hidden" name="id" id="edit-user-id">
            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="nom" id="edit-user-nom" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Prénom</label>
                <input type="text" name="prenom" id="edit-user-prenom" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Login</label>
                <input type="text" name="login" id="edit-user-login" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Nouveau mot de passe <small class="text-muted">(laisser vide pour ne pas changer)</small></label>
                <input type="password" name="mdp" class="form-control">
            </div>
            <div class="form-group">
                <label>Rôle</label>
                <select name="type_utilisateur" id="edit-user-type" class="form-control" required>
                    <option value="visiteur">Visiteur</option>
                    <option value="comptable">Comptable</option>
                    <option value="admin">Administrateur</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Enregistrer</button>
        </form>
    </div>
</div>

<!-- Modal : ajouter forfait -->
<div id="modal-add-forfait" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-add-forfait')">&times;</span>
        <h3>Ajouter un frais forfaitaire</h3>
        <form method="post" action="<?php echo url('index.php?action=add_forfait'); ?>">
            <div class="form-group">
                <label>Libellé</label>
                <input type="text" name="libelle" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Montant (€)</label>
                <input type="number" name="montant" class="form-control" step="0.01" min="0.01" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Ajouter</button>
        </form>
    </div>
</div>

<!-- Modal : modifier forfait -->
<div id="modal-edit-forfait" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-edit-forfait')">&times;</span>
        <h3>Modifier le frais forfaitaire</h3>
        <form method="post" action="<?php echo url('index.php?action=edit_forfait'); ?>">
            <input type="hidden" name="id" id="edit-forfait-id">
            <div class="form-group">
                <label>Libellé</label>
                <input type="text" name="libelle" id="edit-forfait-libelle" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Montant (€)</label>
                <input type="number" name="montant" id="edit-forfait-montant" class="form-control" step="0.01" min="0.01" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Enregistrer</button>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).style.display = 'block';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
window.onclick = function(e) {
    document.querySelectorAll('.modal').forEach(function(m) {
        if (e.target === m) m.style.display = 'none';
    });
};
function openEditUser(u) {
    document.getElementById('edit-user-id').value     = u.id;
    document.getElementById('edit-user-nom').value    = u.nom;
    document.getElementById('edit-user-prenom').value = u.prenom;
    document.getElementById('edit-user-login').value  = u.login;
    document.getElementById('edit-user-type').value   = u.type_utilisateur;
    openModal('modal-edit-user');
}
function openEditForfait(f) {
    document.getElementById('edit-forfait-id').value      = f.id;
    document.getElementById('edit-forfait-libelle').value = f.libelle;
    document.getElementById('edit-forfait-montant').value = f.montant;
    openModal('modal-edit-forfait');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>