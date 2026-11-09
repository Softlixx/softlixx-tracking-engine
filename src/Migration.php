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
            // Strip SQL line comments (-- ...) and block comments (/* ... */)
            $cleanQ = preg_replace('/--.*$/m', '', $q);
            $cleanQ = preg_replace('/\/\*.*?\*\//s', '', $cleanQ);
            $cleanQ = trim($cleanQ);
            
            if (empty($cleanQ)) {
                continue;
            }
            try {
                $db->exec($cleanQ);
                $executed++;
            } catch (Exception $e) {
                // Ignore table already exists errors
                if (!str_contains($e->getMessage(), 'already exists')) {
                    error_log("Migration notice on query: " . $e->getMessage());
                }
            }
        }

        // Create or update default admin user
        $adminUser = 'admin';
        $adminPass = 'Softlixx#2026!Trk';
        $passHash = password_hash($adminPass, PASSWORD_BCRYPT);
        $rawApiKey = $config['api_key'] ?? $config['app']['api_key'] ?? 'stk_live_89f02c61d5ab3744e892c55e94b150937c229987a0';
        $apiKeyHash = hash('sha256', $rawApiKey);

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
