<?php
declare(strict_types=1);

namespace Softlixx\Tracking;

use PDO;
use Exception;

class Migration
{
    public static function run(array $config): array
    {
        $db = Database::getConnection();
        $schemaFile = dirname(__DIR__) . '/schema.sql';
        
        if (!file_exists($schemaFile)) {
            return ['success' => false, 'error' => 'schema.sql not found'];
        }

        $sql = file_get_contents($schemaFile);
        $queries = array_filter(array_map('trim', explode(';', $sql)));

        $executed = 0;
        foreach ($queries as $q) {
            if (empty($q) || str_starts_with($q, '--') || str_starts_with($q, '/*')) {
                continue;
            }
            try {
                $db->exec($q);
                $executed++;
            } catch (Exception $e) {
                // Ignore table already exists errors
                if (!str_contains($e->getMessage(), 'already exists')) {
                    error_log("Migration notice on query [{$q}]: " . $e->getMessage());
                }
            }
        }

        // Create or update default admin user
        $adminUser = 'admin';
        $adminPass = 'Softlixx#2026!Trk';
        $passHash = password_hash($adminPass, PASSWORD_BCRYPT);
        $apiKeyHash = hash('sha256', $config['api_key']);

        try {
            $stmt = $db->prepare("
                INSERT INTO admin_users (username, password_hash, api_key_hash, full_name, email, created_at)
                VALUES (:u, :p, :k, 'Softlixx Admin', 'admin@softlixx.com', NOW())
                ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), api_key_hash = VALUES(api_key_hash)
            ");
            $stmt->execute([
                ':u' => $adminUser,
                ':p' => $passHash,
                ':k' => $apiKeyHash
            ]);
        } catch (Exception $e) {
            error_log("Admin user creation notice: " . $e->getMessage());
        }

        return [
            'success'  => true,
            'queries'  => $executed,
            'message'  => 'Database schema synchronized and admin account active.'
        ];
    }
}
