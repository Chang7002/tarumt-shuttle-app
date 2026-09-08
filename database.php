<?php
declare(strict_types=1);

class Database {
    private static ?mysqli $instance = null;

    public static function getConnection(): mysqli {
        if (self::$instance === null) {
            $host = $_ENV['DB_HOST'] ?? 'shuttle-db-instance.cxahvxc84ilj.us-east-1.rds.amazonaws.com';
            $user = $_ENV['DB_USER'] ?? 'admin';
            $pass = $_ENV['DB_PASS'] ?? 'Tarumt2026Pass!';
            $db   = $_ENV['DB_NAME'] ?? 'shuttle_db';
            $port = (int)($_ENV['DB_PORT'] ?? 3306);

            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            try {
                self::$instance = new mysqli($host, $user, $pass, $db, $port);
                self::$instance->set_charset("utf8mb4");
            } catch (mysqli_sql_exception $e) {
                error_log("Database connection error: " . $e->getMessage());
                throw new RuntimeException("Unable to connect to service backend.");
            }
        }
        return self::$instance;
    }
}