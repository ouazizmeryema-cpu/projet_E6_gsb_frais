<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>GSB Frais</title>
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="<?php echo url('index.php'); ?>" style="text-decoration: none;">
                    <svg width="200" height="60" viewBox="0 0 200 60" role="img" xmlns="http://www.w3.org/2000/svg">
                        <title>Logo GSB</title>
                        <rect x="0" y="0" width="200" height="60" rx="10" fill="#e8f4fd"/>
                        <ellipse cx="22" cy="32" rx="9" ry="14" fill="#4db8a0" transform="rotate(-20 22 32)"/>
                        <ellipse cx="32" cy="25" rx="7" ry="11" fill="#3aa88e" transform="rotate(15 32 25)"/>
                        <ellipse cx="14" cy="26" rx="6" ry="10" fill="#5dcfb5" transform="rotate(-35 14 26)"/>
                        <line x1="27" y1="46" x2="27" y2="55" stroke="#3aa88e" stroke-width="2" stroke-linecap="round"/>
                        <rect x="20" y="53" width="14" height="5" rx="2" fill="#a0c8e0"/>
                        <text x="110" y="28" text-anchor="middle" font-family="Arial, sans-serif" font-size="20" font-weight="900" fill="#1a7abf" letter-spacing="2">GSB</text>
                        <line x1="50" y1="35" x2="180" y2="35" stroke="#a0c8e0" stroke-width="0.8"/>
                        <text x="115" y="48" text-anchor="middle" font-family="Arial, sans-serif" font-size="7" fill="#5a9cbf" letter-spacing="1.5">GALAXY SWISS BOURDIN</text>
                    </svg>
                </a>
            </div>
            <div class="nav-menu">
                <?php if (isAuthenticated()): ?>
                    <span class="user-info"><?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?></span>
                    <span class="user-type"><?php echo ucfirst($_SESSION['user_type']); ?></span>
                    <a href="<?php echo url('index.php'); ?>" class="nav-link">Tableau de bord</a>
                    <a href="<?php echo url('logout.php'); ?>" class="nav-link btn-logout">Déconnexion</a>
                <?php else: ?>
                    <a href="<?php echo url('login.php'); ?>" class="nav-link">Connexion</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="main-content">
        <div class="container">