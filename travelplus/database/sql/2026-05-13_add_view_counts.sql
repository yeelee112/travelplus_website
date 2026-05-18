ALTER TABLE `tours`
    ADD COLUMN `view_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `is_featured`;

ALTER TABLE `blogs`
    ADD COLUMN `view_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `is_featured`;
