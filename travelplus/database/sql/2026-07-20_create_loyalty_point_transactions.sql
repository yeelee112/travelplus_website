CREATE TABLE IF NOT EXISTS `loyalty_point_transactions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `booking_id` INT UNSIGNED DEFAULT NULL,
  `event_key` CHAR(64) NOT NULL,
  `type` VARCHAR(40) NOT NULL,
  `points` INT NOT NULL,
  `amount_vnd` DECIMAL(14,2) DEFAULT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_loyalty_point_event` (`event_key`),
  KEY `idx_loyalty_points_user_created` (`user_id`, `created_at`),
  KEY `idx_loyalty_points_booking` (`booking_id`),
  CONSTRAINT `fk_loyalty_points_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_loyalty_points_booking`
    FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `loyalty_point_transactions` (
  `user_id`,
  `booking_id`,
  `event_key`,
  `type`,
  `points`,
  `amount_vnd`,
  `description`,
  `created_at`
)
SELECT
  `booking`.`user_id`,
  `booking`.`id`,
  SHA2(CONCAT('loyalty-initial|', `booking`.`id`), 256),
  'booking_earned',
  FLOOR(`booking`.`amount_paid_vnd` / 10000),
  `booking`.`amount_paid_vnd`,
  `booking`.`booking_code`,
  NOW()
FROM `bookings` AS `booking`
WHERE `booking`.`user_id` IS NOT NULL
  AND `booking`.`payment_status` = 'paid'
  AND `booking`.`amount_paid_vnd` >= 10000
  AND NOT EXISTS (
    SELECT 1
    FROM `loyalty_point_transactions` AS `existing`
    WHERE `existing`.`booking_id` = `booking`.`id`
  )
ON DUPLICATE KEY UPDATE `event_key` = VALUES(`event_key`);
