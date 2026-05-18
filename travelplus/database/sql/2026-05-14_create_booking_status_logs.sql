CREATE TABLE IF NOT EXISTS `booking_status_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` INT UNSIGNED NOT NULL,
  `from_status` VARCHAR(50) DEFAULT NULL,
  `to_status` VARCHAR(50) NOT NULL,
  `actor_user_id` INT UNSIGNED DEFAULT NULL,
  `actor_name` VARCHAR(255) DEFAULT NULL,
  `actor_email` VARCHAR(255) DEFAULT NULL,
  `note` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_booking_status_logs_booking_id` (`booking_id`),
  KEY `idx_booking_status_logs_actor_user_id` (`actor_user_id`),
  KEY `idx_booking_status_logs_created_at` (`created_at`),
  CONSTRAINT `fk_booking_status_logs_booking_id`
    FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_booking_status_logs_actor_user_id`
    FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
