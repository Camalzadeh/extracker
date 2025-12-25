<?php

class Database {
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO {
        if (self::$pdo === null) {
            $config = require __DIR__ . '/config.inc.php';
            
            $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8";
            
            try {
                self::$pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $tzLookup = $config['timezone'] ?? 'Asia/Baku';
                $mysqlOffset = (new DateTime('now', new DateTimeZone($tzLookup)))->format('P');
                self::$pdo->exec("SET time_zone='$mysqlOffset';");

            } catch (PDOException $e) {
                die("Database Connection Error: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }

    private function __clone() {}
}
?>
