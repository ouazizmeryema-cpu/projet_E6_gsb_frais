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
            $login    = trim($_POST['login']    ?? '');
            $password = trim($_POST['password'] ?? '');

            if (!empty($login) && !empty($password)) {
                $user = $this->userModel->authenticate($login, $password);

                if ($user) {
                    // Régénération de l'ID de session pour prévenir la fixation de session
                    session_regenerate_id(true);

                    $_SESSION['user_id']     = $user['id'];
                    $_SESSION['user_nom']    = $user['nom'];
                    $_SESSION['user_prenom'] = $user['prenom'];
                    $_SESSION['user_type']   = $user['type_utilisateur'];

                    redirect('index.php');
                } else {
                    $error = 'Identifiants incorrects. Vérifiez votre login et mot de passe.';
                }
            } else {
                $error = 'Veuillez remplir tous les champs.';
            }
        }

        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function logout() {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        redirect('login.php');
    }
}
