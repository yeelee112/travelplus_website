-- Fix missing checkout columns used by booking/payment flows.
-- Run once in the active Travel Plus database.
-- The statements are conditional, so running this file again is safe.

SET @db_name := DATABASE();

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `tours` ADD COLUMN `single_room_supplement` INT NULL AFTER `sale_price`',
        'SELECT ''tours.single_room_supplement already exists'' AS message'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'tours'
      AND COLUMN_NAME = 'single_room_supplement'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `bookings` ADD COLUMN `single_room_requested` TINYINT(1) NOT NULL DEFAULT 0 AFTER `infant_price`',
        'SELECT ''bookings.single_room_requested already exists'' AS message'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'bookings'
      AND COLUMN_NAME = 'single_room_requested'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `bookings` ADD COLUMN `single_room_supplement_vnd` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `single_room_requested`',
        'SELECT ''bookings.single_room_supplement_vnd already exists'' AS message'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'bookings'
      AND COLUMN_NAME = 'single_room_supplement_vnd'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `bookings` ADD COLUMN `subtotal_vnd` DECIMAL(12,2) NULL AFTER `single_room_supplement_vnd`',
        'SELECT ''bookings.subtotal_vnd already exists'' AS message'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'bookings'
      AND COLUMN_NAME = 'subtotal_vnd'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `bookings` ADD COLUMN `discount_amount_vnd` DECIMAL(12,2) NULL AFTER `subtotal_vnd`',
        'SELECT ''bookings.discount_amount_vnd already exists'' AS message'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'bookings'
      AND COLUMN_NAME = 'discount_amount_vnd'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `bookings` ADD COLUMN `coupon_id` INT NULL AFTER `discount_amount_vnd`',
        'SELECT ''bookings.coupon_id already exists'' AS message'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'bookings'
      AND COLUMN_NAME = 'coupon_id'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `bookings` ADD COLUMN `coupon_code` VARCHAR(50) NULL AFTER `coupon_id`',
        'SELECT ''bookings.coupon_code already exists'' AS message'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'bookings'
      AND COLUMN_NAME = 'coupon_code'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `bookings` ADD COLUMN `coupon_snapshot` TEXT NULL AFTER `coupon_code`',
        'SELECT ''bookings.coupon_snapshot already exists'' AS message'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'bookings'
      AND COLUMN_NAME = 'coupon_snapshot'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
