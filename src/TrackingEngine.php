<?php
declare(strict_types=1);

namespace Softlixx\Tracking;

use PDO;
use Exception;

class TrackingEngine
{
    /**
     * Binary data for a 1x1 transparent GIF (43 bytes).
     */
    private const TRANSPARENT_GIF_BYTES = "\x47\x49\x46\x38\x39\x61\x01\x00\x01\x00\x80\x00\x00\xff\xff\xff\x00\x00\x00\x21\xf9\x04\x01\x00\x00\x00\x00\x2c\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02\x44\x01\x00\x3b";

    public static function hashToken(string $rawToken): string
    {
        return hash('sha256', $rawToken);
    }

    /**
     * Emits standard anti-caching HTTP headers.
     */
    public static function sendNoCacheHeaders(string $contentType): void
    {
        header("Content-Type: {$contentType}");
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
        header("X-Content-Type-Options: nosniff");
    }

    /**
     * Handles an incoming open-tracking pixel request.
     */
    public static function handleOpenTracking(string $rawToken): void
    {
        self::sendNoCacheHeaders("image/gif");

        if (!empty($rawToken)) {
            $tokenHash = self::hashToken($rawToken);
            $eval = BotDetector::evaluateRequest();
            $ip = BotDetector::getClientIp();
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

            try {
                $db = Database::getConnection();

                // 1. Find matching campaign email
                $stmt = $db->prepare("SELECT id, recipient_email, first_opened_at, open_count FROM campaign_emails WHERE tracking_token_hash = :hash LIMIT 1");
                $stmt->execute([':hash' => $tokenHash]);
                $email = $stmt->fetch();

                if ($email) {
                    $emailId = (int)$email['id'];
                    $isAuto = $eval['is_automated'] ? 1 : 0;
                    $now = date('Y-m-d H:i:s');

                    // 2. Insert event record
                    $evStmt = $db->prepare("
                        INSERT INTO email_events (campaign_email_id, event_type, event_timestamp, event_source, user_agent, ip_metadata, is_automated, detection_reason)
                        VALUES (:eid, 'open', :ts, 'pixel_tracker', :ua, :ip_meta, :is_auto, :reason)
                    ");
                    $ipMeta = json_encode(['ip' => $ip, 'type' => $eval['type']]);
                    $evStmt->execute([
                        ':eid'     => $emailId,
                        ':ts'      => $now,
                        ':ua'      => $ua,
                        ':ip_meta' => $ipMeta,
                        ':is_auto' => $isAuto,
                        ':reason'  => $eval['reason']
                    ]);

                    // 3. Update campaign email summary stats
                    $firstOpenUpdate = empty($email['first_opened_at']) ? ", first_opened_at = '{$now}'" : "";
                    $countColumn = $eval['is_automated'] ? "bot_open_count = bot_open_count + 1" : "human_open_count = human_open_count + 1";

                    $upStmt = $db->prepare("
                        UPDATE campaign_emails 
                        SET open_count = open_count + 1,
                            {$countColumn},
                            last_opened_at = :last_opened
                            {$firstOpenUpdate}
                        WHERE id = :id
                    ");
                    $upStmt->execute([
                        ':last_opened' => $now,
                        ':id'          => $emailId
                    ]);
                }
            } catch (Exception $e) {
                error_log("Open tracking error: " . $e->getMessage());
            }
        }

        // Always output the transparent pixel
        echo self::TRANSPARENT_GIF_BYTES;
        exit;
    }

    /**
     * Handles an incoming click tracking request and performs a safe redirect.
     */
    public static function handleClickTracking(string $rawToken): void
    {
        if (empty($rawToken)) {
            header("Location: https://softlixx.com");
            exit;
        }

        $tokenHash = self::hashToken($rawToken);
        $eval = BotDetector::evaluateRequest();
        $ip = BotDetector::getClientIp();
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $destination = 'https://softlixx.com';

        try {
            $db = Database::getConnection();

            // 1. Find destination link
            $stmt = $db->prepare("SELECT id, campaign_email_id, destination_url FROM tracking_links WHERE link_token_hash = :hash LIMIT 1");
            $stmt->execute([':hash' => $tokenHash]);
            $link = $stmt->fetch();

            if ($link) {
                $linkId = (int)$link['id'];
                $emailId = (int)$link['campaign_email_id'];
                $destUrl = $link['destination_url'];

                // Validate destination to prevent open redirect abuse
                if (filter_var($destUrl, FILTER_VALIDATE_URL) && (str_starts_with($destUrl, 'http://') || str_starts_with($destUrl, 'https://'))) {
                    $destination = $destUrl;
                }

                $isAuto = $eval['is_automated'] ? 1 : 0;
                $now = date('Y-m-d H:i:s');

                // 2. Insert click event
                $evStmt = $db->prepare("
                    INSERT INTO email_events (campaign_email_id, event_type, event_timestamp, event_source, user_agent, ip_metadata, is_automated, detection_reason)
                    VALUES (:eid, 'click', :ts, 'link_redirect', :ua, :ip_meta, :is_auto, :reason)
                ");
                $ipMeta = json_encode(['ip' => $ip, 'link_id' => $linkId, 'type' => $eval['type']]);
                $evStmt->execute([
                    ':eid'     => $emailId,
                    ':ts'      => $now,
                    ':ua'      => $ua,
                    ':ip_meta' => $ipMeta,
                    ':is_auto' => $isAuto,
                    ':reason'  => $eval['reason']
                ]);

                // 3. Update click counts
                $db->prepare("UPDATE tracking_links SET click_count = click_count + 1 WHERE id = :lid")->execute([':lid' => $linkId]);

                if (!$eval['is_automated']) {
                    $db->prepare("UPDATE campaign_emails SET unique_click_count = unique_click_count + 1 WHERE id = :eid")->execute([':eid' => $emailId]);
                }
            }
        } catch (Exception $e) {
            error_log("Click tracking error: " . $e->getMessage());
        }

        // Safe 302 redirect
        header("Location: {$destination}", true, 302);
        exit;
    }

    /**
     * Checks if an email is suppressed.
     */
    public static function isSuppressed(string $email): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT id FROM email_suppressions WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => strtolower(trim($email))]);
            return (bool)$stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Adds an email to suppression list.
     */
    public static function addSuppression(string $email, string $reason = 'unsubscribe', ?string $details = null): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO email_suppressions (email, suppression_reason, details, created_at)
                VALUES (:email, :reason, :details, NOW())
                ON DUPLICATE KEY UPDATE suppression_reason = VALUES(suppression_reason), details = VALUES(details)
            ");
            return $stmt->execute([
                ':email'   => strtolower(trim($email)),
                ':reason'  => $reason,
                ':details' => $details
            ]);
        } catch (Exception $e) {
            error_log("Suppression error: " . $e->getMessage());
            return false;
        }
    }
}
