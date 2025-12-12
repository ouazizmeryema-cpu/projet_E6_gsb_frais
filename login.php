<?php
require_once 'config/config.php';

if (isAuthenticated()) {
    header('Location: /index.php');
    exit;
}

require_once 'controllers/AuthController.php';
$authController = new AuthController();
$authController->login();
