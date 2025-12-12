<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>GSB Frais</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h1>GSB Frais</h1>
            </div>
            <div class="nav-menu">
                <?php if (isAuthenticated()): ?>
                    <span class="user-info"><?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?></span>
                    <span class="user-type"><?php echo ucfirst($_SESSION['user_type']); ?></span>
                    <a href="/index.php" class="nav-link">Tableau de bord</a>
                    <a href="/logout.php" class="nav-link btn-logout">Déconnexion</a>
                <?php else: ?>
                    <a href="/login.php" class="nav-link">Connexion</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="main-content">
        <div class="container">

