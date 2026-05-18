ALTER TABLE `users`
    ADD COLUMN `is_admin` TINYINT(1) NOT NULL DEFAULT 0 AFTER `phone`;

CREATE INDEX `idx_users_is_admin` ON `users` (`is_admin`);

-- Example:
-- UPDATE `users`
-- SET `is_admin` = 1
-- WHERE `email` IN ('an.chauh@travelplusvn.com');
