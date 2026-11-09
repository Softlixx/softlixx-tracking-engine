<?php
declare(strict_types=1);

namespace Softlixx\Tracking;

use Exception;

class Auth
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 86400 * 7,
                'path'     => '/',
                'domain'   => '',
                'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    public static function checkAdmin(): bool
    {
        self::startSession();
        return !empty($_SESSION['softlixx_admin_id']);
    }

    public static function requireAdmin(): void
    {
        if (!self::checkAdmin()) {
            header("Location: /login");
            exit;
        }
    }

    public static function login(string $username, string $password): bool
    {
        self::startSession();
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT id, username, password_hash, full_name, email FROM admin_users WHERE username = :u LIMIT 1");
            $stmt->execute([':u' => trim($username)]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['softlixx_admin_id'] = (int)$user['id'];
                $_SESSION['softlixx_username'] = $user['username'];
                $_SESSION['softlixx_name']     = $user['full_name'];

                $db->prepare("UPDATE admin_users SET last_login_at = NOW() WHERE id = :id")->execute([':id' => $user['id']]);
                return true;
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
        }
        return false;
    }

    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function generateCsrfToken(): string
    {
        self::startSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken(?string $token): bool
    {
        self::startSession();
        return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Authenticates Python integration requests via Bearer API Token.
     */
    public static function verifyApiToken(): bool
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($authHeader, 'Bearer ')) {
            return false;
        }

        $rawToken = trim(substr($authHeader, 7));
        if (empty($rawToken)) {
            return false;
        }

        $tokenHash = hash('sha256', $rawToken);

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT id FROM admin_users WHERE api_key_hash = :hash LIMIT 1");
            $stmt->execute([':hash' => $tokenHash]);
            return (bool)$stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }
}
