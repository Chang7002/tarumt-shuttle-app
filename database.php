<?php
declare(strict_types=1);

class Database {
    private static ?mysqli $instance = null;

    // Prevent direct object creation
    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): mysqli {
        // If connection exists, verify it's still active
        if (self::$instance !== null) {
            try {
                if (!self::$instance->ping()) {
                    self::$instance = null; // Reset dead handle
                }
            } catch (mysqli_sql_exception $e) {
                self::$instance = null;
            }
        }

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

    public static function closeConnection(): void {
        if (self::$instance !== null) {
            self::$instance->close();
            self::$instance = null;
        }
    }
}
