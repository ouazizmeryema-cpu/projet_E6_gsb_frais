<?php
require_once __DIR__ . '/../models/User.php';

class AdminController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function dashboard() {
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }
}

