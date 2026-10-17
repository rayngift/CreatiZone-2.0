<?php
// Seule et unique connexion à la base de données (PDO).
class Database
{
    private static ?Database $instance = null;
    private PDO $conn;

    private function __construct()
    {
        $host = 'localhost';
        $name = 'creatizone';
        $user = 'root';
        $pass = '';
        try {
            $this->conn = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            http_response_code(500);
            die('Connexion à la base de données impossible. Vérifiez config/database.php et importez sql/schema.sql.');
        }
    }

    public static function get(): PDO
    {
        return (self::$instance ??= new Database())->conn;
    }
}
