<?php
$pageTitle = 'Tableau de bord - Visiteur';
include __DIR__ . '/../includes/header.php';

$moisActuel = date('Ym');
$moisFormate = date('m/Y');
?>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Opération effectuée avec succès !</div>
<?php endif; ?>

<div class="dashboard-section">
    <h3>Fiche de frais du mois de <?php echo $moisFormate; ?></h3>
    
    <?php if ($fiche && $fiche['id_etat'] == 2): ?>
        <div class="alert alert-info">Cette fiche est clôturée et en attente de validation.</div>
    <?php elseif ($fiche && $fiche['id_etat'] >= 3): ?>
        <div class="alert alert-warning">Cette fiche ne peut plus être modifiée (<?php echo htmlspecialchars($fiche['etat_libelle']); ?>).</div>
    <?php endif; ?>
    
    <!-- Frais forfaitaires -->
    <div class="card">
        <h4>Frais forfaitaires</h4>
        <form method="POST" action="/index.php?action=save_frais_forfait">
            <input type="hidden" name="mois" value="<?php echo $moisActuel; ?>">
            <table class="table">
                <thead>
                    <tr>
                        <th>Type de frais</th>
                        <th>Montant unitaire</th>
                        <th>Quantité</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $lignesMap = [];
                    foreach ($lignesForfait as $ligne) {
                        $lignesMap[$ligne['id_frais_forfait']] = $ligne;
                    }
                    
                    $totalForfait = 0;
                    foreach ($fraisForfaits as $frais): 
                        $ligne = $lignesMap[$frais['id']] ?? null;
                        $quantite = $ligne ? $ligne['quantite'] : 0;
                        $total = $quantite * $frais['montant'];
                        $totalForfait += $total;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($frais['libelle']); ?></td>
                            <td><?php echo number_format($frais['montant'], 2, ',', ' '); ?> €</td>
                            <td>
                                <input type="number" 
                                       name="quantite_<?php echo $frais['id']; ?>" 
                                       value="<?php echo $quantite; ?>" 
                                       min="0" 
                                       class="form-control"
                                       <?php echo ($fiche && $fiche['id_etat'] >= 2) ? 'disabled' : ''; ?>>
                            </td>
                            <td><?php echo number_format($total, 2, ',', ' '); ?> €</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Total frais forfaitaires</th>
                        <th><?php echo number_format($totalForfait, 2, ',', ' '); ?> €</th>
                    </tr>
                </tfoot>
            </table>
            <?php if (!$fiche || $fiche['id_etat'] < 2): ?>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Frais hors forfait -->
    <div class="card">
        <h4>Frais hors forfait</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Libellé</th>
                    <th>Montant</th>
                    <th>Justificatif</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($fraisHorsForfait)): ?>
                    <tr>
                        <td colspan="5" class="text-center">Aucun frais hors forfait</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $totalHorsForfait = 0;
                    foreach ($fraisHorsForfait as $frais): 
                        $totalHorsForfait += $frais['montant'];
                    ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($frais['date_frais'])); ?></td>
                            <td><?php echo htmlspecialchars($frais['libelle']); ?></td>
                            <td><?php echo number_format($frais['montant'], 2, ',', ' '); ?> €</td>
                            <td>
                                <?php if ($frais['justificatif']): ?>
                                    <a href="/uploads/<?php echo htmlspecialchars($frais['justificatif']); ?>" target="_blank" class="btn btn-sm">Voir</a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$fiche || $fiche['id_etat'] < 2): ?>
                                    <form method="POST" action="/index.php?action=delete_frais_hors_forfait" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce frais ?');">
                                        <input type="hidden" name="id" value="<?php echo $frais['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2">Total frais hors forfait</th>
                    <th><?php echo number_format($totalHorsForfait, 2, ',', ' '); ?> €</th>
                    <th colspan="2"></th>
                </tr>
            </tfoot>
        </table>
        
        <?php if (!$fiche || $fiche['id_etat'] < 2): ?>
            <button onclick="document.getElementById('modal-add-frais').style.display='block'" class="btn btn-primary">Ajouter un frais hors forfait</button>
        <?php endif; ?>
    </div>
    
    <!-- Total -->
    <div class="card">
        <h4>Récapitulatif</h4>
        <table class="table">
            <tr>
                <th>Total frais forfaitaires</th>
                <td><?php echo number_format($totalForfait, 2, ',', ' '); ?> €</td>
            </tr>
            <tr>
                <th>Total frais hors forfait</th>
                <td><?php echo number_format($totalHorsForfait, 2, ',', ' '); ?> €</td>
            </tr>
            <tr>
                <th><strong>Total général</strong></th>
                <td><strong><?php echo number_format($totalForfait + $totalHorsForfait, 2, ',', ' '); ?> €</strong></td>
            </tr>
        </table>
        
        <?php if (!$fiche || $fiche['id_etat'] < 2): ?>
            <form method="POST" action="/index.php?action=cloturer_mois" onsubmit="return confirm('Êtes-vous sûr de vouloir clôturer ce mois ? Vous ne pourrez plus modifier les frais.');">
                <input type="hidden" name="mois" value="<?php echo $moisActuel; ?>">
                <button type="submit" class="btn btn-warning">Clôturer le mois</button>
            </form>
        <?php endif; ?>
    </div>
    
    <!-- Historique -->
    <div class="card">
        <h4>Historique des fiches</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Mois</th>
                    <th>État</th>
                    <th>Montant validé</th>
                    <th>Date de clôture</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($historique)): ?>
                    <tr>
                        <td colspan="4" class="text-center">Aucune fiche</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($historique as $h): ?>
                        <tr>
                            <td><?php echo substr($h['mois'], 4, 2) . '/' . substr($h['mois'], 0, 4); ?></td>
                            <td><?php echo htmlspecialchars($h['etat_libelle']); ?></td>
                            <td><?php echo number_format($h['montant_valide'], 2, ',', ' '); ?> €</td>
                            <td><?php echo $h['date_cloture'] ? date('d/m/Y H:i', strtotime($h['date_cloture'])) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal pour ajouter un frais hors forfait -->
<div id="modal-add-frais" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('modal-add-frais').style.display='none'">&times;</span>
        <h3>Ajouter un frais hors forfait</h3>
        <form method="POST" action="/index.php?action=add_frais_hors_forfait" enctype="multipart/form-data">
            <input type="hidden" name="mois" value="<?php echo $moisActuel; ?>">
            
            <div class="form-group">
                <label for="date_frais">Date</label>
                <input type="date" id="date_frais" name="date_frais" required class="form-control">
            </div>
            
            <div class="form-group">
                <label for="libelle">Libellé</label>
                <input type="text" id="libelle" name="libelle" required class="form-control">
            </div>
            
            <div class="form-group">
                <label for="montant">Montant (€)</label>
                <input type="number" id="montant" name="montant" step="0.01" min="0" required class="form-control">
            </div>
            
            <div class="form-group">
                <label for="justificatif">Justificatif (optionnel)</label>
                <input type="file" id="justificatif" name="justificatif" accept="image/*,.pdf" class="form-control">
            </div>
            
            <button type="submit" class="btn btn-primary">Ajouter</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal-add-frais').style.display='none'">Annuler</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

