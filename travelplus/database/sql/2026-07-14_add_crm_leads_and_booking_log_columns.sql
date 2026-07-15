-- Add CRM leads and payment reconciliation log fields.
-- Safe to run more than once on the current database.

CREATE TABLE IF NOT EXISTS `booking_status_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `booking_id` INT NOT NULL,
  `from_status` VARCHAR(40) DEFAULT NULL,
  `to_status` VARCHAR(40) NOT NULL,
  `amount_paid_vnd` DECIMAL(12,2) DEFAULT NULL,
  `provider_reference` VARCHAR(120) DEFAULT NULL,
  `actor_user_id` INT DEFAULT NULL,
  `actor_name` VARCHAR(150) DEFAULT NULL,
  `actor_email` VARCHAR(190) DEFAULT NULL,
  `note` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_booking_status_logs_booking_id` (`booking_id`),
  KEY `idx_booking_status_logs_to_status` (`to_status`),
  KEY `idx_booking_status_logs_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @schema_name := DATABASE();

SET @sql := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE `booking_status_logs` ADD COLUMN `amount_paid_vnd` DECIMAL(12,2) DEFAULT NULL AFTER `to_status`',
    'SELECT ''booking_status_logs.amount_paid_vnd already exists'' AS message'
  )
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @schema_name
    AND TABLE_NAME = 'booking_status_logs'
    AND COLUMN_NAME = 'amount_paid_vnd'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE `booking_status_logs` ADD COLUMN `provider_reference` VARCHAR(120) DEFAULT NULL AFTER `amount_paid_vnd`',
    'SELECT ''booking_status_logs.provider_reference already exists'' AS message'
  )
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @schema_name
    AND TABLE_NAME = 'booking_status_logs'
    AND COLUMN_NAME = 'provider_reference'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS `crm_leads` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `source` VARCHAR(40) NOT NULL,
  `stage` VARCHAR(30) NOT NULL DEFAULT 'new',
  `priority` VARCHAR(20) NOT NULL DEFAULT 'normal',
  `customer_name` VARCHAR(160) DEFAULT NULL,
  `customer_email` VARCHAR(190) DEFAULT NULL,
  `customer_phone` VARCHAR(40) DEFAULT NULL,
  `service_type` VARCHAR(80) DEFAULT NULL,
  `interest_title` VARCHAR(255) DEFAULT NULL,
  `interest_url` VARCHAR(500) DEFAULT NULL,
  `destination` VARCHAR(160) DEFAULT NULL,
  `travel_date` VARCHAR(80) DEFAULT NULL,
  `travelers` VARCHAR(80) DEFAULT NULL,
  `budget` VARCHAR(160) DEFAULT NULL,
  `message` TEXT DEFAULT NULL,
  `booking_id` INT DEFAULT NULL,
  `booking_code` VARCHAR(40) DEFAULT NULL,
  `last_contacted_at` DATETIME DEFAULT NULL,
  `assigned_user_id` INT DEFAULT NULL,
  `internal_note` TEXT DEFAULT NULL,
  `metadata` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_crm_leads_source` (`source`),
  KEY `idx_crm_leads_stage` (`stage`),
  KEY `idx_crm_leads_priority` (`priority`),
  KEY `idx_crm_leads_customer_email` (`customer_email`),
  KEY `idx_crm_leads_customer_phone` (`customer_phone`),
  KEY `idx_crm_leads_booking_id` (`booking_id`),
  KEY `idx_crm_leads_updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `booking_email_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `booking_id` INT DEFAULT NULL,
  `booking_code` VARCHAR(40) DEFAULT NULL,
  `email_type` VARCHAR(80) NOT NULL,
  `recipient_email` VARCHAR(190) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'sent',
  `error_message` TEXT DEFAULT NULL,
  `sent_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_booking_email_logs_booking_id` (`booking_id`),
  KEY `idx_booking_email_logs_booking_code` (`booking_code`),
  KEY `idx_booking_email_logs_email_type` (`email_type`),
  KEY `idx_booking_email_logs_recipient_email` (`recipient_email`),
  KEY `idx_booking_email_logs_status` (`status`),
  KEY `idx_booking_email_logs_sent_at` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
