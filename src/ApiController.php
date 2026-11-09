<?php
declare(strict_types=1);

namespace Softlixx\Tracking;

use PDO;
use Exception;

class ApiController
{
    private static function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private static function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /**
     * POST /api/v1/sync-client
     */
    public static function syncClient(): void
    {
        if (!Auth::verifyApiToken()) {
            self::jsonResponse(['error' => 'Unauthorized: Invalid or missing API token'], 401);
        }

        $input = self::getJsonInput();
        $extId = trim($input['external_client_id'] ?? '');
        $business = trim($input['business_name'] ?? '');
        $email = strtolower(trim($input['email'] ?? ''));
        $website = trim($input['website'] ?? '');
        $industry = trim($input['industry'] ?? '');

        if (empty($extId) || empty($business) || empty($email)) {
            self::jsonResponse(['error' => 'Missing required fields: external_client_id, business_name, email'], 422);
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO clients (external_client_id, business_name, email, website, industry, created_at)
                VALUES (:ext, :biz, :em, :web, :ind, NOW())
                ON DUPLICATE KEY UPDATE 
                    business_name = VALUES(business_name),
                    email = VALUES(email),
                    website = VALUES(website),
                    industry = VALUES(industry)
            ");
            $stmt->execute([
                ':ext' => $extId,
                ':biz' => $business,
                ':em'  => $email,
                ':web' => $website,
                ':ind' => $industry
            ]);

            $idStmt = $db->prepare("SELECT id FROM clients WHERE external_client_id = :ext LIMIT 1");
            $idStmt->execute([':ext' => $extId]);
            $client = $idStmt->fetch();

            self::jsonResponse([
                'success' => true,
                'client_id' => (int)$client['id'],
                'external_client_id' => $extId,
                'is_suppressed' => TrackingEngine::isSuppressed($email)
            ]);
        } catch (Exception $e) {
            self::jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/register-email
     */
    public static function registerEmail(): void
    {
        if (!Auth::verifyApiToken()) {
            self::jsonResponse(['error' => 'Unauthorized: Invalid or missing API token'], 401);
        }

        $input = self::getJsonInput();
        $campaignName = trim($input['campaign_name'] ?? 'Default Campaign');
        $extClientId = trim($input['external_client_id'] ?? '');
        $recipientEmail = strtolower(trim($input['recipient_email'] ?? ''));
        $subject = trim($input['subject'] ?? 'Outreach');
        $seq = (int)($input['email_sequence'] ?? 1);
        $content = trim($input['email_content'] ?? $input['email_html'] ?? '');
        $targetLinks = $input['target_links'] ?? []; // Array of URLs

        // Auto-extract links from HTML content if target_links not explicitly supplied
        if (empty($targetLinks) && !empty($content)) {
            preg_match_all('/href=["\'](https?:\/\/[^"\']+)["\']/i', $content, $m);
            if (!empty($m[1])) {
                $targetLinks = array_values(array_unique($m[1]));
            }
        }

        if (empty($recipientEmail)) {
            self::jsonResponse(['error' => 'recipient_email is required'], 422);
        }

        // Check suppression
        if (TrackingEngine::isSuppressed($recipientEmail)) {
            self::jsonResponse([
                'error' => 'Recipient is suppressed due to previous unsubscribe or bounce',
                'suppressed' => true
            ], 403);
        }

        try {
            $db = Database::getConnection();

            // 1. Get or create campaign
            $campStmt = $db->prepare("SELECT id FROM campaigns WHERE campaign_name = :name LIMIT 1");
            $campStmt->execute([':name' => $campaignName]);
            $camp = $campStmt->fetch();
            if (!$camp) {
                $db->prepare("INSERT INTO campaigns (campaign_name, status, created_at) VALUES (:n, 'active', NOW())")
                   ->execute([':n' => $campaignName]);
                $campaignId = (int)$db->lastInsertId();
            } else {
                $campaignId = (int)$camp['id'];
            }

            // 2. Resolve client_id if external_client_id passed
            $clientId = null;
            if (!empty($extClientId)) {
                $cStmt = $db->prepare("SELECT id FROM clients WHERE external_client_id = :ext LIMIT 1");
                $cStmt->execute([':ext' => $extClientId]);
                $cRow = $cStmt->fetch();
                if ($cRow) {
                    $clientId = (int)$cRow['id'];
                }
            }

            // 3. Generate unique tracking token
            $rawTrackingToken = 'trk_' . bin2hex(random_bytes(16));
            $trackingTokenHash = TrackingEngine::hashToken($rawTrackingToken);

            // 4. Insert into campaign_emails
            $insStmt = $db->prepare("
                INSERT INTO campaign_emails 
                (campaign_id, client_id, tracking_token_hash, recipient_email, subject, email_sequence, email_content, delivery_status, created_at)
                VALUES (:cid, :clid, :thash, :rec, :sub, :seq, :cont, 'queued', NOW())
            ");
            $insStmt->execute([
                ':cid'   => $campaignId,
                ':clid'  => $clientId,
                ':thash' => $trackingTokenHash,
                ':rec'   => $recipientEmail,
                ':sub'   => $subject,
                ':seq'   => $seq,
                ':cont'  => $content
            ]);
            $emailId = (int)$db->lastInsertId();

            // 5. Generate tracked redirect links
            $trackedLinks = [];
            $baseUrl = 'https://tracking.softlixx.com';

            foreach ($targetLinks as $originalUrl) {
                $origTrim = trim($originalUrl);
                if (empty($origTrim)) continue;

                $rawLinkToken = 'lnk_' . bin2hex(random_bytes(12));
                $linkHash = TrackingEngine::hashToken($rawLinkToken);

                $db->prepare("
                    INSERT INTO tracking_links (campaign_email_id, link_token_hash, destination_url, created_at)
                    VALUES (:eid, :lhash, :dest, NOW())
                ")->execute([
                    ':eid'   => $emailId,
                    ':lhash' => $linkHash,
                    ':dest'  => $origTrim
                ]);

                $trackedLinks[] = [
                    'original_url' => $origTrim,
                    'tracked_url'  => "{$baseUrl}/click/{$rawLinkToken}"
                ];
            }

            $pixelUrl = "{$baseUrl}/open/{$rawTrackingToken}";
            $pixelHtml = '<img src="' . $pixelUrl . '" width="1" height="1" alt="" style="display:none !important;visibility:hidden;opacity:0;max-height:0;max-width:0;line-height:0;" />';

            self::jsonResponse([
                'success'           => true,
                'campaign_email_id' => $emailId,
                'campaign_id'       => $campaignId,
                'tracking_token'    => $rawTrackingToken,
                'pixel_url'         => $pixelUrl,
                'pixel_html'        => $pixelHtml,
                'tracked_links'     => $trackedLinks
            ]);
        } catch (Exception $e) {
            self::jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/mark-sent
     * (Strict requirement: only mark sent AFTER SMTP 250 confirmation)
     */
    public static function markSent(): void
    {
        if (!Auth::verifyApiToken()) {
            self::jsonResponse(['error' => 'Unauthorized: Invalid or missing API token'], 401);
        }

        $input = self::getJsonInput();
        $emailId = (int)($input['campaign_email_id'] ?? 0);

        if ($emailId <= 0) {
            self::jsonResponse(['error' => 'Valid campaign_email_id is required'], 422);
        }

        try {
            $db = Database::getConnection();
            $now = date('Y-m-d H:i:s');

            $db->prepare("
                UPDATE campaign_emails 
                SET delivery_status = 'sent', sent_at = :now 
                WHERE id = :id
            ")->execute([':now' => $now, ':id' => $emailId]);

            // Insert initial 'sent' audit event
            $db->prepare("
                INSERT INTO email_events (campaign_email_id, event_type, event_timestamp, event_source, is_automated, detection_reason)
                VALUES (:eid, 'sent', :now, 'smtp_submission', 0, 'SMTP server accepted message for delivery')
            ")->execute([':eid' => $emailId, ':now' => $now]);

            self::jsonResponse([
                'success' => true,
                'campaign_email_id' => $emailId,
                'status' => 'sent',
                'sent_at' => $now
            ]);
        } catch (Exception $e) {
            self::jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/mark-failed
     */
    public static function markFailed(): void
    {
        if (!Auth::verifyApiToken()) {
            self::jsonResponse(['error' => 'Unauthorized: Invalid or missing API token'], 401);
        }

        $input = self::getJsonInput();
        $emailId = (int)($input['campaign_email_id'] ?? 0);
        $reason = trim($input['error_reason'] ?? 'SMTP submission failure');

        if ($emailId <= 0) {
            self::jsonResponse(['error' => 'Valid campaign_email_id is required'], 422);
        }

        try {
            $db = Database::getConnection();
            $db->prepare("UPDATE campaign_emails SET delivery_status = 'failed' WHERE id = :id")
               ->execute([':id' => $emailId]);

            self::jsonResponse([
                'success' => true,
                'campaign_email_id' => $emailId,
                'status' => 'failed',
                'error_reason' => $reason
            ]);
        } catch (Exception $e) {
            self::jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/log-reply
     */
    public static function logReply(): void
    {
        if (!Auth::verifyApiToken()) {
            self::jsonResponse(['error' => 'Unauthorized: Invalid or missing API token'], 401);
        }

        $input = self::getJsonInput();
        $recipientEmail = strtolower(trim($input['recipient_email'] ?? ''));
        $replyStatus = trim($input['reply_status'] ?? 'replied'); // replied, interested, not_interested
        $details = trim($input['reply_snippet'] ?? '');

        if (empty($recipientEmail)) {
            self::jsonResponse(['error' => 'recipient_email is required'], 422);
        }

        try {
            $db = Database::getConnection();
            $now = date('Y-m-d H:i:s');

            // Find latest email sent to this recipient
            $stmt = $db->prepare("SELECT id FROM campaign_emails WHERE recipient_email = :rec ORDER BY id DESC LIMIT 1");
            $stmt->execute([':rec' => $recipientEmail]);
            $row = $stmt->fetch();

            if ($row) {
                $emailId = (int)$row['id'];
                $db->prepare("
                    UPDATE campaign_emails 
                    SET reply_status = :st, replied_at = :now 
                    WHERE id = :eid
                ")->execute([':st' => $replyStatus, ':now' => $now, ':eid' => $emailId]);

                $db->prepare("
                    INSERT INTO email_events (campaign_email_id, event_type, event_timestamp, event_source, is_automated, detection_reason)
                    VALUES (:eid, 'reply', :now, 'inbox_synchronizer', 0, :reason)
                ")->execute([':eid' => $emailId, ':now' => $now, ':reason' => "Inbound reply registered: {$replyStatus}"]);
            }

            self::jsonResponse([
                'success' => true,
                'recipient_email' => $recipientEmail,
                'reply_status' => $replyStatus
            ]);
        } catch (Exception $e) {
            self::jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/campaign-stats
     */
    public static function getCampaignStats(): void
    {
        if (!Auth::verifyApiToken() && !Auth::checkAdmin()) {
            self::jsonResponse(['error' => 'Unauthorized'], 401);
        }

        try {
            $db = Database::getConnection();
            $campaignId = isset($_GET['campaign_id']) ? (int)$_GET['campaign_id'] : 0;

            $where = $campaignId > 0 ? "WHERE campaign_id = {$campaignId}" : "";

            $stats = $db->query("
                SELECT 
                    COUNT(*) as total_emails,
                    SUM(CASE WHEN delivery_status = 'sent' THEN 1 ELSE 0 END) as sent_count,
                    SUM(CASE WHEN open_count > 0 THEN 1 ELSE 0 END) as opened_count,
                    SUM(human_open_count) as total_human_opens,
                    SUM(bot_open_count) as total_bot_opens,
                    SUM(unique_click_count) as total_unique_clicks,
                    SUM(CASE WHEN reply_status != 'none' THEN 1 ELSE 0 END) as replies_count
                FROM campaign_emails
                {$where}
            ")->fetch();

            self::jsonResponse([
                'success' => true,
                'stats'   => $stats
            ]);
        } catch (Exception $e) {
            self::jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/client-emails
     * Returns tracked emails and engagement statistics for a specific client.
     */
    public static function getClientEmails(): void
    {
        if (!Auth::verifyApiToken() && !Auth::checkAdmin()) {
            self::jsonResponse(['error' => 'Unauthorized'], 401);
        }

        $extId = trim($_GET['external_client_id'] ?? '');
        $email = strtolower(trim($_GET['recipient_email'] ?? ''));

        if (empty($extId) && empty($email)) {
            self::jsonResponse(['error' => 'external_client_id or recipient_email is required'], 422);
        }

        try {
            $db = Database::getConnection();
            $whereClause = [];
            $params = [];
            if (!empty($extId)) {
                $whereClause[] = "c.external_client_id = :ext";
                $params[':ext'] = $extId;
            }
            if (!empty($email)) {
                $whereClause[] = "ce.recipient_email = :em";
                $params[':em'] = $email;
            }
            $whereSql = implode(" OR ", $whereClause);

            $stmt = $db->prepare("
                SELECT ce.*, c.business_name, c.external_client_id
                FROM campaign_emails ce
                LEFT JOIN clients c ON ce.client_id = c.id
                WHERE {$whereSql}
                ORDER BY ce.id DESC
            ");
            $stmt->execute($params);
            $emails = $stmt->fetchAll();

            self::jsonResponse([
                'success' => true,
                'count'   => count($emails),
                'emails'  => $emails
            ]);
        } catch (Exception $e) {
            self::jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
