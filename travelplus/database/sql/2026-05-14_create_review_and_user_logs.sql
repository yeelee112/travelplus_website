CREATE TABLE IF NOT EXISTS `review_status_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `review_id` INT UNSIGNED NOT NULL,
  `from_status` VARCHAR(50) NULL,
  `to_status` VARCHAR(50) NOT NULL,
  `actor_user_id` INT UNSIGNED NULL,
  `actor_name` VARCHAR(255) NULL,
  `actor_email` VARCHAR(255) NULL,
  `note` TEXT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_review_status_logs_review_id` (`review_id`),
  CONSTRAINT `fk_review_status_logs_review_id`
    FOREIGN KEY (`review_id`) REFERENCES `tour_reviews` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_change_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `actor_user_id` INT UNSIGNED NULL,
  `actor_name` VARCHAR(255) NULL,
  `actor_email` VARCHAR(255) NULL,
  `action` VARCHAR(50) NOT NULL,
  `changes_json` LONGTEXT NULL,
  `note` TEXT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_change_logs_user_id` (`user_id`),
  CONSTRAINT `fk_user_change_logs_user_id`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
