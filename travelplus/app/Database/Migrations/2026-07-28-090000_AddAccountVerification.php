<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAccountVerification extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        $this->db->query(
            "ALTER TABLE `users` MODIFY `status` ENUM('pending_verification','active','inactive','blocked') NOT NULL DEFAULT 'active'"
        );

        $columns = [];
        if (! $this->db->fieldExists('email_verified_at', 'users')) {
            $columns[] = 'ADD COLUMN `email_verified_at` DATETIME NULL AFTER `auth_session_version`';
        }
        if (! $this->db->fieldExists('phone_verified_at', 'users')) {
            $columns[] = 'ADD COLUMN `phone_verified_at` DATETIME NULL AFTER `email_verified_at`';
        }
        if (! $this->db->fieldExists('verification_channel', 'users')) {
            $columns[] = 'ADD COLUMN `verification_channel` VARCHAR(20) NULL AFTER `phone_verified_at`';
        }
        if ($columns !== []) {
            $this->db->query('ALTER TABLE `users` ' . implode(', ', $columns));
        }

        if ($this->db->tableExists('account_verification_requests')) {
            return;
        }

        $this->db->query(<<<'SQL'
CREATE TABLE `account_verification_requests` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    }

    public function down()
    {
        if ($this->db->tableExists('account_verification_requests')) {
            $this->db->query('DROP TABLE `account_verification_requests`');
        }

        if (! $this->db->tableExists('users')) {
            return;
        }

        $columns = [];
        foreach (['email_verified_at', 'phone_verified_at', 'verification_channel'] as $field) {
            if ($this->db->fieldExists($field, 'users')) {
                $columns[] = 'DROP COLUMN `' . $field . '`';
            }
        }
        if ($columns !== []) {
            $this->db->query('ALTER TABLE `users` ' . implode(', ', $columns));
        }

        $this->db->query(
            "ALTER TABLE `users` MODIFY `status` ENUM('active','inactive','blocked') NOT NULL DEFAULT 'active'"
        );
    }
}
