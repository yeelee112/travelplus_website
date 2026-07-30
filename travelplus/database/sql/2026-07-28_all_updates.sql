-- Travel Plus - all database updates for 2026-07-28
-- Import this single file in phpMyAdmin on the database used by the website.
-- Existing tables `users` and `bookings` are required.
-- All column/table additions are idempotent and can be run again safely.

SET NAMES utf8mb4;

-- 1. Account verification status and user verification fields.
SET @travelplus_sql = (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'status'
    ),
    'ALTER TABLE `users` MODIFY COLUMN `status` ENUM(''pending_verification'',''active'',''inactive'',''blocked'') NOT NULL DEFAULT ''active''',
    'DO 0'
  )
);
PREPARE travelplus_stmt FROM @travelplus_sql;
EXECUTE travelplus_stmt;
DEALLOCATE PREPARE travelplus_stmt;

SET @travelplus_sql = (
  SELECT IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users')
      AND NOT EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'email_verified_at'),
    'ALTER TABLE `users` ADD COLUMN `email_verified_at` DATETIME NULL',
    'DO 0'
  )
);
PREPARE travelplus_stmt FROM @travelplus_sql;
EXECUTE travelplus_stmt;
DEALLOCATE PREPARE travelplus_stmt;

SET @travelplus_sql = (
  SELECT IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users')
      AND NOT EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'phone_verified_at'),
    'ALTER TABLE `users` ADD COLUMN `phone_verified_at` DATETIME NULL',
    'DO 0'
  )
);
PREPARE travelplus_stmt FROM @travelplus_sql;
EXECUTE travelplus_stmt;
DEALLOCATE PREPARE travelplus_stmt;

SET @travelplus_sql = (
  SELECT IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users')
      AND NOT EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'verification_channel'),
    'ALTER TABLE `users` ADD COLUMN `verification_channel` VARCHAR(20) NULL',
    'DO 0'
  )
);
PREPARE travelplus_stmt FROM @travelplus_sql;
EXECUTE travelplus_stmt;
DEALLOCATE PREPARE travelplus_stmt;

CREATE TABLE IF NOT EXISTS `account_verification_requests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `channel` ENUM('email','zalo') NOT NULL,
  `recipient` VARCHAR(255) NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `attempts` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `max_attempts` SMALLINT UNSIGNED NOT NULL DEFAULT 5,
  `delivery_status` ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
  `provider_message_id` VARCHAR(191) NULL,
  `provider_error_code` VARCHAR(32) NULL,
  `expires_at` DATETIME NOT NULL,
  `verified_at` DATETIME NULL,
  `last_sent_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_account_verification_token` (`token_hash`),
  KEY `idx_account_verification_user_channel` (`user_id`, `channel`, `created_at`),
  KEY `idx_account_verification_expiry` (`delivery_status`, `expires_at`),
  CONSTRAINT `fk_account_verification_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Required structured address fields for user accounts.
SET @travelplus_sql = (
  SELECT IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users')
      AND NOT EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'address'),
    'ALTER TABLE `users` ADD COLUMN `address` VARCHAR(500) NULL',
    'DO 0'
  )
);
PREPARE travelplus_stmt FROM @travelplus_sql;
EXECUTE travelplus_stmt;
DEALLOCATE PREPARE travelplus_stmt;

SET @travelplus_sql = (
  SELECT IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users')
      AND NOT EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'address_line'),
    'ALTER TABLE `users` ADD COLUMN `address_line` VARCHAR(255) NULL',
    'DO 0'
  )
);
PREPARE travelplus_stmt FROM @travelplus_sql;
EXECUTE travelplus_stmt;
DEALLOCATE PREPARE travelplus_stmt;

SET @travelplus_sql = (
  SELECT IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users')
      AND NOT EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'province_code'),
    'ALTER TABLE `users` ADD COLUMN `province_code` VARCHAR(2) NULL',
    'DO 0'
  )
);
PREPARE travelplus_stmt FROM @travelplus_sql;
EXECUTE travelplus_stmt;
DEALLOCATE PREPARE travelplus_stmt;

SET @travelplus_sql = (
  SELECT IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users')
      AND NOT EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'ward_code'),
    'ALTER TABLE `users` ADD COLUMN `ward_code` VARCHAR(5) NULL',
    'DO 0'
  )
);
PREPARE travelplus_stmt FROM @travelplus_sql;
EXECUTE travelplus_stmt;
DEALLOCATE PREPARE travelplus_stmt;

-- 3. Required structured address fields for bookings.
SET @travelplus_sql = (
  SELECT IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bookings')
      AND NOT EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bookings' AND COLUMN_NAME = 'customer_address'),
    'ALTER TABLE `bookings` ADD COLUMN `customer_address` VARCHAR(500) NULL',
    'DO 0'
  )
);
PREPARE travelplus_stmt FROM @travelplus_sql;
EXECUTE travelplus_stmt;
DEALLOCATE PREPARE travelplus_stmt;

SET @travelplus_sql = (
  SELECT IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bookings')
      AND NOT EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bookings' AND COLUMN_NAME = 'customer_address_line'),
    'ALTER TABLE `bookings` ADD COLUMN `customer_address_line` VARCHAR(255) NULL',
    'DO 0'
  )
);
PREPARE travelplus_stmt FROM @travelplus_sql;
EXECUTE travelplus_stmt;
DEALLOCATE PREPARE travelplus_stmt;

SET @travelplus_sql = (
  SELECT IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bookings')
      AND NOT EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bookings' AND COLUMN_NAME = 'customer_province_code'),
    'ALTER TABLE `bookings` ADD COLUMN `customer_province_code` VARCHAR(2) NULL',
    'DO 0'
  )
);
PREPARE travelplus_stmt FROM @travelplus_sql;
EXECUTE travelplus_stmt;
DEALLOCATE PREPARE travelplus_stmt;

SET @travelplus_sql = (
  SELECT IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bookings')
      AND NOT EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bookings' AND COLUMN_NAME = 'customer_ward_code'),
    'ALTER TABLE `bookings` ADD COLUMN `customer_ward_code` VARCHAR(5) NULL',
    'DO 0'
  )
);
PREPARE travelplus_stmt FROM @travelplus_sql;
EXECUTE travelplus_stmt;
DEALLOCATE PREPARE travelplus_stmt;

-- Verification query: expected result is 11 columns and 1 table.
SELECT
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
      AND COLUMN_NAME IN ('email_verified_at','phone_verified_at','verification_channel','address','address_line','province_code','ward_code'))
  +
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bookings'
      AND COLUMN_NAME IN ('customer_address','customer_address_line','customer_province_code','customer_ward_code'))
  AS `installed_columns`,
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'account_verification_requests')
  AS `installed_tables`;
