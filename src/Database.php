<?php
declare(strict_types=1);

namespace Softlixx\Tracking;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];

    public static function init(array $config): void
    {
        self::$config = $config;
    }

    public static function getConnection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        if (empty(self::$config)) {
            // Attempt to load from ../config.php or environment
            $configPath = dirname(__DIR__) . '/config.php';
            if (file_exists($configPath)) {
                self::$config = require $configPath;
            } else {
                self::$config = [
                    'host'     => getenv('DB_HOST') ?: '127.0.0.1',
                    'port'     => getenv('DB_PORT') ?: '3306',
                    'dbname'   => getenv('DB_NAME') ?: 'tracking_softlixx',
                    'username' => getenv('DB_USER') ?: 'root',
                    'password' => getenv('DB_PASS') ?: '',
                    'charset'  => 'utf8mb4'
                ];
            }
        }

        $c = self::$config;
        $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['dbname']};charset={$c['charset']}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            self::$instance = new PDO($dsn, $c['username'], $c['password'], $options);
            return self::$instance;
        } catch (PDOException $e) {
            error_log("Database connection failure: " . $e->getMessage());
            throw new RuntimeException("Database connection could not be established. Please check server configuration.");
        }
    }
}
