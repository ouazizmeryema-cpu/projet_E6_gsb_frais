<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function login() {
        $error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login = $_POST['login'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (!empty($login) && !empty($password)) {
                $user = $this->userModel->authenticate($login, $password);
                
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_nom'] = $user['nom'];
                    $_SESSION['user_prenom'] = $user['prenom'];
                    $_SESSION['user_type'] = $user['type_utilisateur'];
                    
                    header('Location: /index.php');
                    exit;
                } else {
                    $error = 'Identifiants incorrects';
                }
            } else {
                $error = 'Veuillez remplir tous les champs';
            }
        }
        
        require_once __DIR__ . '/../views/auth/login.php';
    }
    
    public function logout() {
        session_destroy();
        header('Location: /login.php');
        exit;
    }
}

