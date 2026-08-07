CREATE TABLE IF NOT EXISTS `loyalty_reward_vouchers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `promotion_code_id` INT NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `reward_key` VARCHAR(50) NOT NULL,
  `points_spent` INT UNSIGNED NOT NULL,
  `voucher_amount_vnd` DECIMAL(12,2) NOT NULL,
  `min_order_vnd` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `status` VARCHAR(20) NOT NULL DEFAULT 'issued',
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_loyalty_reward_code` (`code`),
  UNIQUE KEY `uq_loyalty_reward_promotion` (`promotion_code_id`),
  KEY `idx_loyalty_reward_user_created` (`user_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
