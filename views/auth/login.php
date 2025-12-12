<?php
$pageTitle = 'Connexion';
include __DIR__ . '/../includes/header.php';
?>

<div class="login-container">
    <div class="login-card">
        <h2>Connexion</h2>
        <?php if (isset($error) && $error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" class="login-form">
            <div class="form-group">
                <label for="login">Identifiant</label>
                <input type="text" id="login" name="login" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
        </form>
        
        <div class="login-info">
            <p><strong>Comptes de test :</strong></p>
            <ul>
                <li>Admin : admin / admin123</li>
                <li>Visiteur : visiteur1 / visiteur123</li>
                <li>Comptable : comptable1 / comptable123</li>
            </ul>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

