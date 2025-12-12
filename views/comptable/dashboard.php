<?php
$pageTitle = 'Tableau de bord - Comptable';
include __DIR__ . '/../includes/header.php';
?>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Opération effectuée avec succès !</div>
<?php endif; ?>

<div class="dashboard-section">
    <h3>Fiches de frais en attente de validation</h3>
    
    <?php if (empty($fichesEnAttente)): ?>
        <div class="alert alert-info">Aucune fiche en attente de validation.</div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Visiteur</th>
                    <th>Mois</th>
                    <th>Date de clôture</th>
                    <th>Montant validé</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fichesEnAttente as $fiche): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fiche['prenom'] . ' ' . $fiche['nom']); ?></td>
                        <td><?php echo substr($fiche['mois'], 4, 2) . '/' . substr($fiche['mois'], 0, 4); ?></td>
                        <td><?php echo $fiche['date_cloture'] ? date('d/m/Y H:i', strtotime($fiche['date_cloture'])) : '-'; ?></td>
                        <td><?php echo number_format($fiche['montant_valide'], 2, ',', ' '); ?> €</td>
                        <td>
                            <a href="/index.php?action=voir_fiche&visiteur=<?php echo $fiche['id_visiteur']; ?>&mois=<?php echo $fiche['mois']; ?>" class="btn btn-primary btn-sm">Voir la fiche</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="dashboard-section">
    <h3>Rechercher une fiche par visiteur</h3>
    <form method="GET" action="/index.php" class="form-inline">
        <input type="hidden" name="action" value="voir_fiche">
        <div class="form-group">
            <label for="visiteur">Visiteur</label>
            <select name="visiteur" id="visiteur" required class="form-control">
                <option value="">Sélectionner un visiteur</option>
                <?php foreach ($visiteurs as $visiteur): ?>
                    <option value="<?php echo $visiteur['id']; ?>"><?php echo htmlspecialchars($visiteur['prenom'] . ' ' . $visiteur['nom']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="mois">Mois</label>
            <input type="month" name="mois" id="mois" required class="form-control" value="<?php echo date('Y-m'); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Voir la fiche</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

