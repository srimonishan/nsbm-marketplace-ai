<?php
/**
 * GreenLink Market - Database Configuration
 * Uses PDO with prepared statements for security
 */

class Database {
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    
    private string $host;
    private string $dbname;
    private string $username;
    private string $password;
    private string $charset = 'utf8mb4';

    private function __construct() {
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->dbname = getenv('DB_NAME') ?: 'nsbm_marketplace';
        $this->username = getenv('DB_USER') ?: 'nsbm_user';
        $this->password = getenv('DB_PASS') ?: 'nsbm_pass123';
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        if ($this->connection === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
                ];
                $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            } catch (PDOException $e) {
                error_log("Database Connection Error: " . $e->getMessage());
                throw new Exception("Database connection failed. Please check configuration.");
            }
        }
        return $this->connection;
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
