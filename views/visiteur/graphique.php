<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Évolution des frais</title>
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="main-content container">
    <div class="graphique-container">
        <a href="<?php echo url('index.php'); ?>" class="btn btn-secondary btn-retour">← Retour au tableau de bord</a>

        <h2>Évolution des frais par mois</h2>

        <div class="graphique-card">
            <canvas id="graphFrais"></canvas>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
    const mois   = <?= $moisJson ?>;
    const totaux = <?= $totauxJson ?>;

    const ctx = document.getElementById('graphFrais').getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: mois,
            datasets: [{
                label: 'Frais validés (€)',
                data: totaux,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true },
                title: {
                    display: true,
                    text: 'Évolution des frais par mois'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Montant (€)' }
                },
                x: {
                    title: { display: true, text: 'Mois' }
                }
            }
        }
    });
</script>

</body>
</html>