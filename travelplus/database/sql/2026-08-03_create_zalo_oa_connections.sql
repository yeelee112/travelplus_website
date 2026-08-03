-- Travel Plus - Zalo Official Account OAuth connection storage
-- Run this file once in phpMyAdmin on the selected Travel Plus database.

CREATE TABLE IF NOT EXISTS `zalo_oa_connections` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `oa_id` VARCHAR(100) NOT NULL,
  `oa_name` VARCHAR(190) NULL,
  `app_id` VARCHAR(100) NOT NULL,
  `access_token_enc` TEXT NOT NULL,
  `refresh_token_enc` TEXT NOT NULL,
  `access_token_expires_at` DATETIME NULL,
  `status` ENUM('active','inactive','revoked','error') NOT NULL DEFAULT 'active',
  `last_error` VARCHAR(500) NULL,
  `connected_by` BIGINT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_zalo_oa_id` (`oa_id`),
  KEY `idx_zalo_oa_app_status` (`app_id`, `status`),
  KEY `idx_zalo_oa_expiry` (`status`, `access_token_expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT
  COUNT(*) AS `zalo_oa_table_ready`
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'zalo_oa_connections';
