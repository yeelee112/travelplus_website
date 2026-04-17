-- Tour detail schema update
-- Run this file after selecting the database `travelplus_db`
-- Example:
-- USE travelplus_db;
-- SOURCE tour_detail_schema_update.sql;

SET @db_name = DATABASE();

-- ------------------------------------------------------------
-- tours: add new columns if missing
-- ------------------------------------------------------------

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'tours'
          AND COLUMN_NAME = 'sku'
    ),
    'SELECT ''tours.sku already exists''',
    'ALTER TABLE `tours` ADD COLUMN `sku` VARCHAR(100) NULL AFTER `id`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'tours'
          AND COLUMN_NAME = 'code'
    ),
    'SELECT ''tours.code already exists''',
    'ALTER TABLE `tours` ADD COLUMN `code` VARCHAR(100) NULL AFTER `sku`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'tours'
          AND COLUMN_NAME = 'primary_destination_id'
    ),
    'SELECT ''tours.primary_destination_id already exists''',
    'ALTER TABLE `tours` ADD COLUMN `primary_destination_id` INT NULL AFTER `departure_location_id`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'tours'
          AND COLUMN_NAME = 'min_travelers'
    ),
    'SELECT ''tours.min_travelers already exists''',
    'ALTER TABLE `tours` ADD COLUMN `min_travelers` INT NULL AFTER `duration_nights`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'tours'
          AND COLUMN_NAME = 'max_travelers'
    ),
    'SELECT ''tours.max_travelers already exists''',
    'ALTER TABLE `tours` ADD COLUMN `max_travelers` INT NULL AFTER `min_travelers`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'tours'
          AND COLUMN_NAME = 'base_price'
    ),
    'SELECT ''tours.base_price already exists''',
    'ALTER TABLE `tours` ADD COLUMN `base_price` INT NULL AFTER `max_travelers`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'tours'
          AND COLUMN_NAME = 'sale_price'
    ),
    'SELECT ''tours.sale_price already exists''',
    'ALTER TABLE `tours` ADD COLUMN `sale_price` INT NULL AFTER `base_price`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'tours'
          AND COLUMN_NAME = 'currency'
    ),
    'SELECT ''tours.currency already exists''',
    'ALTER TABLE `tours` ADD COLUMN `currency` VARCHAR(10) NOT NULL DEFAULT ''VND'' AFTER `sale_price`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'tours'
          AND COLUMN_NAME = 'rating_avg'
    ),
    'SELECT ''tours.rating_avg already exists''',
    'ALTER TABLE `tours` ADD COLUMN `rating_avg` DECIMAL(3,2) NULL AFTER `currency`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'tours'
          AND COLUMN_NAME = 'reviews_count'
    ),
    'SELECT ''tours.reviews_count already exists''',
    'ALTER TABLE `tours` ADD COLUMN `reviews_count` INT NOT NULL DEFAULT 0 AFTER `rating_avg`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'tours'
          AND COLUMN_NAME = 'map_embed'
    ),
    'SELECT ''tours.map_embed already exists''',
    'ALTER TABLE `tours` ADD COLUMN `map_embed` TEXT NULL AFTER `thumbnail`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------
-- tour_translations: add new columns if missing
-- ------------------------------------------------------------

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'tour_translations'
          AND COLUMN_NAME = 'overview'
    ),
    'SELECT ''tour_translations.overview already exists''',
    'ALTER TABLE `tour_translations` ADD COLUMN `overview` LONGTEXT NULL AFTER `short_description`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'tour_translations'
          AND COLUMN_NAME = 'booking_policy'
    ),
    'SELECT ''tour_translations.booking_policy already exists''',
    'ALTER TABLE `tour_translations` ADD COLUMN `booking_policy` LONGTEXT NULL AFTER `itinerary`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'tour_translations'
          AND COLUMN_NAME = 'cancellation_policy'
    ),
    'SELECT ''tour_translations.cancellation_policy already exists''',
    'ALTER TABLE `tour_translations` ADD COLUMN `cancellation_policy` LONGTEXT NULL AFTER `booking_policy`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'tour_translations'
          AND COLUMN_NAME = 'price_note'
    ),
    'SELECT ''tour_translations.price_note already exists''',
    'ALTER TABLE `tour_translations` ADD COLUMN `price_note` TEXT NULL AFTER `cancellation_policy`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------
-- Create new tables
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `tour_media` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `tour_id` INT NOT NULL,
    `type` ENUM('cover','gallery','banner','video') NOT NULL DEFAULT 'gallery',
    `file_path` VARCHAR(255) NOT NULL,
    `alt_text` VARCHAR(255) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `idx_tour_media_tour_id` (`tour_id`),
    CONSTRAINT `fk_tour_media_tour_id`
        FOREIGN KEY (`tour_id`) REFERENCES `tours`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tour_itinerary_days` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `tour_id` INT NOT NULL,
    `day_number` INT NOT NULL,
    `meals` VARCHAR(50) NULL,
    `hotel_name` VARCHAR(255) NULL,
    `transport_summary` VARCHAR(255) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `idx_tour_itinerary_days_tour_id` (`tour_id`),
    KEY `idx_tour_itinerary_days_tour_day` (`tour_id`, `day_number`),
    CONSTRAINT `fk_tour_itinerary_days_tour_id`
        FOREIGN KEY (`tour_id`) REFERENCES `tours`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tour_itinerary_day_translations` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `itinerary_day_id` INT NOT NULL,
    `locale` VARCHAR(10) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` LONGTEXT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_tour_itinerary_day_translations_day_locale` (`itinerary_day_id`, `locale`),
    CONSTRAINT `fk_tour_itinerary_day_translations_day_id`
        FOREIGN KEY (`itinerary_day_id`) REFERENCES `tour_itinerary_days`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tour_inclusions` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `tour_id` INT NOT NULL,
    `type` ENUM('included','excluded') NOT NULL DEFAULT 'included',
    `icon` VARCHAR(100) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `idx_tour_inclusions_tour_id` (`tour_id`),
    CONSTRAINT `fk_tour_inclusions_tour_id`
        FOREIGN KEY (`tour_id`) REFERENCES `tours`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tour_inclusion_translations` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `tour_inclusion_id` INT NOT NULL,
    `locale` VARCHAR(10) NOT NULL,
    `label` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_tour_inclusion_translations_item_locale` (`tour_inclusion_id`, `locale`),
    CONSTRAINT `fk_tour_inclusion_translations_item_id`
        FOREIGN KEY (`tour_inclusion_id`) REFERENCES `tour_inclusions`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tour_faqs` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `tour_id` INT NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `idx_tour_faqs_tour_id` (`tour_id`),
    CONSTRAINT `fk_tour_faqs_tour_id`
        FOREIGN KEY (`tour_id`) REFERENCES `tours`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tour_faq_translations` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `faq_id` INT NOT NULL,
    `locale` VARCHAR(10) NOT NULL,
    `question` VARCHAR(255) NOT NULL,
    `answer` LONGTEXT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_tour_faq_translations_faq_locale` (`faq_id`, `locale`),
    CONSTRAINT `fk_tour_faq_translations_faq_id`
        FOREIGN KEY (`faq_id`) REFERENCES `tour_faqs`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tour_reviews` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `tour_id` INT NOT NULL,
    `reviewer_name` VARCHAR(255) NOT NULL,
    `reviewer_email` VARCHAR(255) NULL,
    `rating_overall` DECIMAL(3,2) NULL,
    `rating_destination` DECIMAL(3,2) NULL,
    `rating_transport` DECIMAL(3,2) NULL,
    `rating_value` DECIMAL(3,2) NULL,
    `title` VARCHAR(255) NULL,
    `content` LONGTEXT NULL,
    `status` ENUM('pending','approved','hidden') NOT NULL DEFAULT 'pending',
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `idx_tour_reviews_tour_id` (`tour_id`),
    CONSTRAINT `fk_tour_reviews_tour_id`
        FOREIGN KEY (`tour_id`) REFERENCES `tours`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tour_highlights` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `tour_id` INT NOT NULL,
    `icon` VARCHAR(100) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `idx_tour_highlights_tour_id` (`tour_id`),
    CONSTRAINT `fk_tour_highlights_tour_id`
        FOREIGN KEY (`tour_id`) REFERENCES `tours`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tour_highlight_translations` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `highlight_id` INT NOT NULL,
    `locale` VARCHAR(10) NOT NULL,
    `label` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_tour_highlight_translations_item_locale` (`highlight_id`, `locale`),
    CONSTRAINT `fk_tour_highlight_translations_item_id`
        FOREIGN KEY (`highlight_id`) REFERENCES `tour_highlights`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
