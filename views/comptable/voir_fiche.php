<?php
$pageTitle = 'Fiche de frais - ' . htmlspecialchars($visiteur['prenom'] . ' ' . $visiteur['nom']);
include __DIR__ . '/../includes/header.php';

$moisFormate = substr($mois, 4, 2) . '/' . substr($mois, 0, 4);
?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'commentaire_requis'): ?>
    <div class="alert alert-error">Un commentaire est requis pour refuser un frais.</div>
<?php endif; ?>

<div class="dashboard-section">
    <h3>Fiche de frais de <?php echo htmlspecialchars($visiteur['prenom'] . ' ' . $visiteur['nom']); ?> - <?php echo $moisFormate; ?></h3>
    
    <?php if ($fiche): ?>
        <div class="card">
            <p><strong>État :</strong> <?php echo htmlspecialchars($fiche['etat_libelle']); ?></p>
            <p><strong>Montant validé :</strong> <?php echo number_format($fiche['montant_valide'], 2, ',', ' '); ?> €</p>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">Cette fiche n'existe pas encore.</div>
    <?php endif; ?>
    
    <!-- Frais forfaitaires -->
    <div class="card">
        <h4>Frais forfaitaires</h4>
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
                $totalForfait = 0;
                if (empty($lignesForfait)): ?>
                    <tr>
                        <td colspan="4" class="text-center">Aucun frais forfaitaire</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($lignesForfait as $ligne): 
                        $total = $ligne['quantite'] * $ligne['montant'];
                        $totalForfait += $total;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ligne['libelle']); ?></td>
                            <td><?php echo number_format($ligne['montant'], 2, ',', ' '); ?> €</td>
                            <td><?php echo $ligne['quantite']; ?></td>
                            <td><?php echo number_format($total, 2, ',', ' '); ?> €</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">Total frais forfaitaires</th>
                    <th><?php echo number_format($totalForfait, 2, ',', ' '); ?> €</th>
                </tr>
            </tfoot>
        </table>
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
                    <th>État</th>
                    <th>Commentaire</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalHorsForfait = 0;
                if (empty($fraisHorsForfait)): ?>
                    <tr>
                        <td colspan="7" class="text-center">Aucun frais hors forfait</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($fraisHorsForfait as $frais): 
                        $totalHorsForfait += $frais['montant'];
                        $etatClass = '';
                        $etatText = 'En attente';
                        if ($frais['valide'] === true) {
                            $etatClass = 'text-success';
                            $etatText = 'Accepté';
                        } elseif ($frais['valide'] === false) {
                            $etatClass = 'text-danger';
                            $etatText = 'Refusé';
                        }
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
                            <td class="<?php echo $etatClass; ?>"><?php echo $etatText; ?></td>
                            <td><?php echo $frais['commentaire_comptable'] ? htmlspecialchars($frais['commentaire_comptable']) : '-'; ?></td>
                            <td>
                                <?php if ($frais['valide'] === null): ?>
                                    <form method="POST" action="/index.php?action=valider_frais_hors_forfait" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $frais['id']; ?>">
                                        <input type="hidden" name="id_visiteur" value="<?php echo $visiteur['id']; ?>">
                                        <input type="hidden" name="mois" value="<?php echo $mois; ?>">
                                        <button type="submit" class="btn btn-sm btn-success">Valider</button>
                                    </form>
                                    <button onclick="showRefusModal(<?php echo $frais['id']; ?>)" class="btn btn-sm btn-danger">Refuser</button>
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
                    <th colspan="4"></th>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <!-- Total et actions -->
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
        
        <?php if ($fiche && $fiche['id_etat'] == 2): ?>
            <div class="actions">
                <form method="POST" action="/index.php?action=valider_fiche" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir valider cette fiche ?');">
                    <input type="hidden" name="id_visiteur" value="<?php echo $visiteur['id']; ?>">
                    <input type="hidden" name="mois" value="<?php echo $mois; ?>">
                    <button type="submit" class="btn btn-success">Valider la fiche</button>
                </form>
                <form method="POST" action="/index.php?action=refuser_fiche" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir refuser cette fiche ?');">
                    <input type="hidden" name="id_visiteur" value="<?php echo $visiteur['id']; ?>">
                    <input type="hidden" name="mois" value="<?php echo $mois; ?>">
                    <button type="submit" class="btn btn-danger">Refuser la fiche</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal pour refuser un frais -->
<div id="modal-refus" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('modal-refus').style.display='none'">&times;</span>
        <h3>Refuser un frais hors forfait</h3>
        <form method="POST" action="/index.php?action=refuser_frais_hors_forfait">
            <input type="hidden" name="id" id="refus_id">
            <input type="hidden" name="id_visiteur" value="<?php echo $visiteur['id']; ?>">
            <input type="hidden" name="mois" value="<?php echo $mois; ?>">
            
            <div class="form-group">
                <label for="commentaire">Commentaire (obligatoire)</label>
                <textarea id="commentaire" name="commentaire" required class="form-control" rows="4"></textarea>
            </div>
            
            <button type="submit" class="btn btn-danger">Refuser</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal-refus').style.display='none'">Annuler</button>
        </form>
    </div>
</div>

<script>
function showRefusModal(id) {
    document.getElementById('refus_id').value = id;
    document.getElementById('modal-refus').style.display = 'block';
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

