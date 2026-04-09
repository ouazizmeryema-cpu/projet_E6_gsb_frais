<?php
require_once 'config/config.php';

if (isAuthenticated()) {
    redirect('index.php');
}

require_once 'controllers/AuthController.php';
$authController = new AuthController();
$authController->login();
