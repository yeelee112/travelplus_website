ALTER TABLE `bookings`
    ADD COLUMN `membership_tier_key` VARCHAR(20) NULL AFTER `subtotal_vnd`,
    ADD COLUMN `membership_discount_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `membership_tier_key`,
    ADD COLUMN `membership_discount_amount_vnd` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `membership_discount_rate`;
