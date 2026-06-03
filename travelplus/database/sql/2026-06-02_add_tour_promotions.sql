-- Add homepage promotional tour controls.
-- Run once in the Travel Plus database.

ALTER TABLE `tours`
    ADD COLUMN `is_promotion` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_featured`,
    ADD COLUMN `promotion_badge` VARCHAR(80) NULL AFTER `is_promotion`,
    ADD COLUMN `promotion_ends_at` DATETIME NULL AFTER `promotion_badge`,
    ADD COLUMN `promotion_sort` INT NOT NULL DEFAULT 0 AFTER `promotion_ends_at`;

CREATE INDEX `idx_tours_home_promotion`
    ON `tours` (`is_promotion`, `promotion_sort`, `promotion_ends_at`);

-- Example: mark several tours as homepage promotions.
-- Replace the ids and dates with your real tour ids and campaign deadline.
-- UPDATE `tours`
-- SET
--     `is_promotion` = 1,
--     `promotion_badge` = 'Tour khuyến mãi',
--     `promotion_ends_at` = '2026-07-31 23:59:59',
--     `promotion_sort` = 10
-- WHERE `id` IN (1, 2, 3, 4);

-- Example: remove a tour from homepage promotions.
-- UPDATE `tours`
-- SET
--     `is_promotion` = 0,
--     `promotion_badge` = NULL,
--     `promotion_ends_at` = NULL,
--     `promotion_sort` = 0
-- WHERE `id` = 1;
