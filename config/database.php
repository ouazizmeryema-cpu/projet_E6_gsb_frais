<?php
/**
 * Configuration de la base de données
 */
class Database {
    private static $instance = null;
    private $connection;

    private $host     = 'localhost';
    private $dbname   = 'gsb_frais';
    private $username = 'root';
    private $password = '';
    private $charset  = 'utf8mb4';

    private function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // Affiche une page d'erreur propre plutot qu'un die() brut
            http_response_code(503);
            echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">
                  <title>Erreur de connexion</title></head><body>
                  <h2>Impossible de se connecter à  la base de données.</h2>
                  <p>VÃ©rifiez les paramétres dans <code>config/database.php</code> et que le serveur MySQL est dÃ©marrÃ©.</p>
                  <p><em>DÃ©tail technique : ' . htmlspecialchars($e->getMessage()) . '</em></p>
                  </body></html>';
            exit;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    private function __clone() {}

    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
