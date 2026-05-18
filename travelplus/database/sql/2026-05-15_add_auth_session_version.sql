ALTER TABLE `users`
    ADD COLUMN `auth_session_version` INT NOT NULL DEFAULT 0 AFTER `status`;
