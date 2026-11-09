-- ============================================================
-- SOFTLIXX EMAIL INTELLIGENCE & TRACKING SYSTEM
-- Schema: MySQL / MariaDB (PHP 8.x + Hostinger Shared Hosting)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. CAMPAIGNS
CREATE TABLE IF NOT EXISTS `campaigns` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `campaign_name` VARCHAR(191) NOT NULL,
  `description` TEXT NULL,
  `status` ENUM('draft', 'active', 'paused', 'completed', 'archived') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_campaign_status` (`status`),
  INDEX `idx_campaign_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. CLIENTS (Synchronized with Python Lead Engine)
CREATE TABLE IF NOT EXISTS `clients` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `external_client_id` VARCHAR(128) NOT NULL,
  `business_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `website` VARCHAR(255) NULL,
  `industry` VARCHAR(128) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_external_client_id` (`external_client_id`),
  INDEX `idx_client_email` (`email`),
  INDEX `idx_client_business` (`business_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. CAMPAIGN EMAILS (Individual dispatched emails)
CREATE TABLE IF NOT EXISTS `campaign_emails` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `campaign_id` INT UNSIGNED NOT NULL,
  `client_id` INT UNSIGNED NULL,
  `tracking_token_hash` VARCHAR(64) NOT NULL,
  `recipient_email` VARCHAR(191) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `email_sequence` INT UNSIGNED NOT NULL DEFAULT 1,
  `email_content` MEDIUMTEXT NULL,
  `sent_at` DATETIME NULL,
  `delivery_status` ENUM('queued', 'sent', 'failed', 'bounced') NOT NULL DEFAULT 'queued',
  `first_opened_at` DATETIME NULL,
  `last_opened_at` DATETIME NULL,
  `open_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `human_open_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `bot_open_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `unique_click_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `reply_status` ENUM('none', 'replied', 'interested', 'not_interested', 'auto_responder') NOT NULL DEFAULT 'none',
  `replied_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_tracking_token_hash` (`tracking_token_hash`),
  INDEX `idx_campaign_id` (`campaign_id`),
  INDEX `idx_client_id` (`client_id`),
  INDEX `idx_recipient_email` (`recipient_email`),
  INDEX `idx_delivery_status` (`delivery_status`),
  INDEX `idx_sent_at` (`sent_at`),
  CONSTRAINT `fk_campaign_emails_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_campaign_emails_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. EMAIL EVENTS (Detailed audit telemetry with bot & privacy detection)
CREATE TABLE IF NOT EXISTS `email_events` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `campaign_email_id` INT UNSIGNED NOT NULL,
  `event_type` ENUM('sent', 'open', 'click', 'reply', 'bounce', 'unsubscribe') NOT NULL,
  `event_timestamp` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `event_source` VARCHAR(64) NOT NULL DEFAULT 'web_tracker',
  `user_agent` TEXT NULL,
  `ip_metadata` JSON NULL,
  `is_automated` TINYINT(1) NOT NULL DEFAULT 0,
  `detection_reason` VARCHAR(191) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_event_type` (`event_type`),
  INDEX `idx_event_timestamp` (`event_timestamp`),
  INDEX `idx_event_email_id` (`campaign_email_id`),
  INDEX `idx_is_automated` (`is_automated`),
  CONSTRAINT `fk_email_events_campaign_email` FOREIGN KEY (`campaign_email_id`) REFERENCES `campaign_emails` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. TRACKING LINKS (Link click redirects)
CREATE TABLE IF NOT EXISTS `tracking_links` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `campaign_email_id` INT UNSIGNED NOT NULL,
  `link_token_hash` VARCHAR(64) NOT NULL,
  `destination_url` TEXT NOT NULL,
  `click_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_link_token_hash` (`link_token_hash`),
  INDEX `idx_link_email_id` (`campaign_email_id`),
  CONSTRAINT `fk_tracking_links_campaign_email` FOREIGN KEY (`campaign_email_id`) REFERENCES `campaign_emails` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. EMAIL SUPPRESSIONS (Unsubscribes, hard bounces, complaints)
CREATE TABLE IF NOT EXISTS `email_suppressions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(191) NOT NULL,
  `suppression_reason` ENUM('unsubscribe', 'hard_bounce', 'complaint', 'manual') NOT NULL DEFAULT 'unsubscribe',
  `details` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_suppression_email` (`email`),
  INDEX `idx_suppression_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. ADMIN USERS (Secure authentication for the dashboard)
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(64) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `api_key_hash` VARCHAR(64) NOT NULL,
  `full_name` VARCHAR(128) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login_at` DATETIME NULL,
  UNIQUE KEY `uniq_username` (`username`),
  UNIQUE KEY `uniq_admin_email` (`email`),
  UNIQUE KEY `uniq_api_key_hash` (`api_key_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
