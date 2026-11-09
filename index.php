<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

/**
 * Softlixx Email Tracking & Intelligence Engine
 * Front Controller & Router
 */

$configPath = file_exists(__DIR__ . '/config.php') ? __DIR__ . '/config.php' : __DIR__ . '/../config.php';
$baseDir = dirname($configPath);

// One-time secure provisioning if config.php does not exist yet
if (!file_exists($configPath)) {
    $rawRoute = $_GET['route'] ?? '';
    $route = trim($rawRoute, '/');
    if (empty($route)) {
        $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $route = trim($uriPath, '/');
    }
    
    if ($route === 'provision' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $secret = $input['secret'] ?? $_GET['secret'] ?? '';
        if ($secret === 'provision_softlixx_trk_2026_x87y') {
            $dbHost = addslashes($input['db_host'] ?? '127.0.0.1');
            $dbName = addslashes($input['db_name'] ?? 'u668550229_trk_softlixx');
            $dbUser = addslashes($input['db_user'] ?? 'u668550229_trk_user');
            $dbPass = addslashes($input['db_pass'] ?? '');
            $apiKey = addslashes($input['api_key'] ?? '');
            $installSecret = addslashes($input['install_secret'] ?? 'init_softlixx_2026_trk_a9b8c7');
            
            $configContent = "<?php\ndeclare(strict_types=1);\nreturn [\n"
                . "    'host' => '{$dbHost}',\n"
                . "    'port' => 3306,\n"
                . "    'dbname' => '{$dbName}',\n"
                . "    'username' => '{$dbUser}',\n"
                . "    'password' => '{$dbPass}',\n"
                . "    'charset' => 'utf8mb4',\n"
                . "    'api_key' => '{$apiKey}',\n"
                . "    'secret_key' => 'sec_99a8b13c048fe7d2681a95e7c813098741e2f',\n"
                . "    'install_secret' => '{$installSecret}',\n"
                . "    'base_url' => 'https://tracking.softlixx.com',\n"
                . "    'app_name' => 'Softlixx Email Intelligence',\n"
                . "    'db' => [\n"
                . "        'host' => '{$dbHost}',\n"
                . "        'port' => 3306,\n"
                . "        'dbname' => '{$dbName}',\n"
                . "        'username' => '{$dbUser}',\n"
                . "        'password' => '{$dbPass}',\n"
                . "        'charset' => 'utf8mb4'\n"
                . "    ],\n"
                . "    'app' => [\n"
                . "        'name' => 'Softlixx Email Intelligence',\n"
                . "        'url' => 'https://tracking.softlixx.com',\n"
                . "        'api_key' => '{$apiKey}',\n"
                . "        'install_secret' => '{$installSecret}'\n"
                . "    ]\n"
                . "];\n";
            file_put_contents($baseDir . '/config.php', $configContent);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Configuration written']);
            exit;
        }
    }
    http_response_code(503);
    die("Softlixx Tracking requires initial provisioning.");
}

$appConfig = require_once $configPath;
require_once $baseDir . '/src/Database.php';
require_once $baseDir . '/src/BotDetector.php';
require_once $baseDir . '/src/TrackingEngine.php';
require_once $baseDir . '/src/Auth.php';
require_once $baseDir . '/src/Migration.php';
require_once $baseDir . '/src/ApiController.php';

use Softlixx\Tracking\Auth;
use Softlixx\Tracking\Database;
use Softlixx\Tracking\TrackingEngine;
use Softlixx\Tracking\ApiController;
use Softlixx\Tracking\Migration;

// Initialize database with loaded configuration
Database::init($appConfig);

// Determine clean request route
$rawRoute = $_GET['route'] ?? '';
$route = trim($rawRoute, '/');

// Also support PATH_INFO or REQUEST_URI if route query param is empty
if (empty($route)) {
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $route = trim($uriPath, '/');
}

// -------------------------------------------------------------
// 1. OPEN TRACKING PIXEL: /open/{token}
// -------------------------------------------------------------
if ($route === 'open' || str_starts_with($route, 'open/')) {
    $token = $_GET['token'] ?? '';
    if (empty($token) && str_starts_with($route, 'open/')) {
        $token = substr($route, 5);
    }
    TrackingEngine::handleOpenTracking(trim($token));
    exit;
}

// -------------------------------------------------------------
// 2. LINK CLICK TRACKING & REDIRECT: /click/{token}
// -------------------------------------------------------------
if ($route === 'click' || str_starts_with($route, 'click/')) {
    $token = $_GET['token'] ?? '';
    if (empty($token) && str_starts_with($route, 'click/')) {
        $token = substr($route, 6);
    }
    TrackingEngine::handleClickTracking(trim($token));
    exit;
}

// -------------------------------------------------------------
// 3. UNSUBSCRIBE & SUPPRESSION: /unsubscribe/{token}
// -------------------------------------------------------------
if ($route === 'unsubscribe' || str_starts_with($route, 'unsubscribe/')) {
    $token = $_GET['token'] ?? '';
    if (empty($token) && str_starts_with($route, 'unsubscribe/')) {
        $token = substr($route, 12);
    }
    $token = trim($token);
    $email = '';
    
    if (!empty($token)) {
        try {
            $db = Database::getConnection();
            $hash = TrackingEngine::hashToken($token);
            $stmt = $db->prepare("SELECT id, recipient_email FROM campaign_emails WHERE tracking_token_hash = :hash LIMIT 1");
            $stmt->execute([':hash' => $hash]);
            $row = $stmt->fetch();
            
            if ($row) {
                $email = strtolower(trim($row['recipient_email']));
                $emailId = (int)$row['id'];
                
                // Add suppression
                $supStmt = $db->prepare("INSERT IGNORE INTO email_suppressions (email, suppression_reason, source) VALUES (:em, 'unsubscribe', 'recipient_click')");
                $supStmt->execute([':em' => $email]);
                
                // Log event
                $evStmt = $db->prepare("INSERT INTO email_events (campaign_email_id, event_type, event_timestamp, event_source, user_agent, ip_metadata, is_automated) VALUES (:eid, 'unsubscribe', NOW(), 'unsubscribe_link', :ua, :ip, 0)");
                $evStmt->execute([
                    ':eid' => $emailId,
                    ':ua'  => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                    ':ip'  => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                ]);
            }
        } catch (\Exception $e) {
            error_log("Unsubscribe error: " . $e->getMessage());
        }
    }
    
    require $baseDir . '/views/unsubscribed.php';
    exit;
}

// -------------------------------------------------------------
// 4. DATABASE INITIALIZATION / MIGRATION: /init-db
// -------------------------------------------------------------
if ($route === 'init-db') {
    $secret = $_GET['secret'] ?? '';
    $expectedSecret = $appConfig['install_secret'] ?? 'init_softlixx_2026_trk_a9b8c7';
    
    if ($secret !== $expectedSecret) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Forbidden: Invalid migration secret']);
        exit;
    }
    
    try {
        $res = Migration::run($appConfig);
        header('Content-Type: application/json');
        echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } catch (\Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => $e->getMessage(),
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    exit;
}

// -------------------------------------------------------------
// 5. REST API ENDPOINTS: /api/v1/...
// -------------------------------------------------------------
if (str_starts_with($route, 'api/v1/')) {
    $endpoint = substr($route, 7);
    
    switch ($endpoint) {
        case 'sync-client':
            ApiController::syncClient();
            break;
            
        case 'register-email':
            ApiController::registerEmail();
            break;
            
        case 'mark-sent':
            ApiController::markSent();
            break;
            
        case 'mark-failed':
            ApiController::markFailed();
            break;
            
        case 'log-reply':
            ApiController::logReply();
            break;
            
        case 'campaign-stats':
            ApiController::getCampaignStats();
            break;
            
        default:
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Endpoint not found', 'endpoint' => $endpoint]);
            break;
    }
    exit;
}

// -------------------------------------------------------------
// 6. AUTHENTICATION (WEB)
// -------------------------------------------------------------
Auth::startSession();

if ($route === 'login') {
    if (Auth::checkAdmin()) {
        header("Location: /");
        exit;
    }
    
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        if (Auth::login($username, $password)) {
            header("Location: /");
            exit;
        } else {
            $error = 'Invalid administrative credentials. Please verify username and password.';
        }
    }
    
    $csrfToken = Auth::generateCsrfToken();
    require $baseDir . '/views/login.php';
    exit;
}

if ($route === 'logout') {
    Auth::logout();
    header("Location: /login");
    exit;
}

// All subsequent pages require active admin login
Auth::requireAdmin();

$activeRoute = $route ?: 'dashboard';
$adminUser = Auth::getAdminUser();

// -------------------------------------------------------------
// 7. WEB ADMIN PAGES
// -------------------------------------------------------------
try {
    $db = Database::getConnection();
    
    switch ($activeRoute) {
        case '':
        case 'dashboard':
            // High-level KPI aggregates
            $totalSent = (int)$db->query("SELECT COUNT(*) FROM campaign_emails WHERE delivery_status = 'sent'")->fetchColumn();
            $humanOpens = (int)$db->query("SELECT COUNT(DISTINCT campaign_email_id) FROM email_events WHERE event_type = 'open' AND is_automated = 0")->fetchColumn();
            $botOpens = (int)$db->query("SELECT COUNT(*) FROM email_events WHERE event_type = 'open' AND is_automated = 1")->fetchColumn();
            $totalClicks = (int)$db->query("SELECT COUNT(DISTINCT campaign_email_id) FROM email_events WHERE event_type = 'click' AND is_automated = 0")->fetchColumn();
            $totalReplies = (int)$db->query("SELECT COUNT(*) FROM campaign_emails WHERE reply_status = 'replied'")->fetchColumn();
            $activeClients = (int)$db->query("SELECT COUNT(*) FROM clients")->fetchColumn();
            $activeCampaigns = (int)$db->query("SELECT COUNT(*) FROM campaigns")->fetchColumn();

            // Recent 20 Events
            $stmt = $db->query("
                SELECT e.*, ce.recipient_email, ce.subject, c.business_name
                FROM email_events e
                LEFT JOIN campaign_emails ce ON e.campaign_email_id = ce.id
                LEFT JOIN clients c ON ce.client_id = c.id
                ORDER BY e.id DESC LIMIT 20
            ");
            $recentEvents = $stmt->fetchAll();

            $pageTitle = 'Dashboard Overview';
            $contentView = $baseDir . '/views/dashboard.php';
            break;

        case 'campaigns':
            $stmt = $db->query("
                SELECT c.*,
                    COUNT(ce.id) as emails_count,
                    SUM(CASE WHEN ce.delivery_status = 'sent' THEN 1 ELSE 0 END) as sent_count,
                    SUM(CASE WHEN ce.open_count > 0 THEN 1 ELSE 0 END) as opened_count,
                    SUM(ce.unique_click_count) as clicks_count,
                    SUM(CASE WHEN ce.reply_status = 'replied' THEN 1 ELSE 0 END) as replied_count
                FROM campaigns c
                LEFT JOIN campaign_emails ce ON c.id = ce.campaign_id
                GROUP BY c.id
                ORDER BY c.id DESC
            ");
            $campaigns = $stmt->fetchAll();
            $pageTitle = 'Outreach Campaigns';
            $contentView = $baseDir . '/views/campaigns.php';
            break;

        case 'clients':
            $stmt = $db->query("
                SELECT c.*,
                    COUNT(ce.id) as total_emails,
                    MAX(ce.sent_at) as last_contacted_at,
                    SUM(CASE WHEN ce.reply_status = 'replied' THEN 1 ELSE 0 END) as replied_count
                FROM clients c
                LEFT JOIN campaign_emails ce ON c.id = ce.client_id
                GROUP BY c.id
                ORDER BY c.id DESC
                LIMIT 100
            ");
            $clients = $stmt->fetchAll();
            $pageTitle = 'Directory & Clients';
            $contentView = $baseDir . '/views/clients.php';
            break;

        case 'events':
            $stmt = $db->query("
                SELECT e.*, ce.recipient_email, ce.subject, c.business_name
                FROM email_events e
                LEFT JOIN campaign_emails ce ON e.campaign_email_id = ce.id
                LEFT JOIN clients c ON ce.client_id = c.id
                ORDER BY e.id DESC
                LIMIT 150
            ");
            $events = $stmt->fetchAll();
            $pageTitle = 'Live Event Stream';
            $contentView = $baseDir . '/views/events.php';
            break;

        case 'suppressions':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                Auth::verifyCsrfToken($_POST['csrf_token'] ?? '');
                $em = strtolower(trim($_POST['email'] ?? ''));
                $reason = trim($_POST['reason'] ?? 'manual');
                if ($em) {
                    $stmt = $db->prepare("INSERT IGNORE INTO email_suppressions (email, suppression_reason, source) VALUES (:e, :r, 'admin_manual')");
                    $stmt->execute([':e' => $em, ':r' => $reason]);
                }
                header("Location: /suppressions");
                exit;
            }
            $suppressions = $db->query("SELECT * FROM email_suppressions ORDER BY id DESC LIMIT 200")->fetchAll();
            $pageTitle = 'Email Suppressions';
            $contentView = $baseDir . '/views/suppressions.php';
            break;

        case 'api-docs':
            $pageTitle = 'API Documentation';
            $contentView = $baseDir . '/views/api_docs.php';
            break;

        default:
            // Check client detail: clients/{id}
            if (str_starts_with($activeRoute, 'clients/')) {
                $clientId = (int)substr($activeRoute, 8);
                $stmt = $db->prepare("SELECT * FROM clients WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => $clientId]);
                $client = $stmt->fetch();
                if (!$client) {
                    header("Location: /clients");
                    exit;
                }
                
                $emailStmt = $db->prepare("SELECT * FROM campaign_emails WHERE client_id = :cid ORDER BY id DESC");
                $emailStmt->execute([':cid' => $clientId]);
                $emails = $emailStmt->fetchAll();
                
                $pageTitle = 'Client: ' . htmlspecialchars($client['business_name']);
                $contentView = $baseDir . '/views/client_detail.php';
                break;
            }

            http_response_code(404);
            $pageTitle = 'Page Not Found';
            $contentView = null;
            echo "404 Not Found";
            exit;
    }

    // Render with standard layout
    require $baseDir . '/views/layout.php';

} catch (\Exception $e) {
    http_response_code(500);
    echo "Server Error: " . htmlspecialchars($e->getMessage());
    exit;
}
