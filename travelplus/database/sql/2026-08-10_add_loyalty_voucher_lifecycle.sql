ALTER TABLE `loyalty_reward_vouchers`
    ADD COLUMN `booking_id` INT UNSIGNED NULL AFTER `status`,
    ADD COLUMN `reserved_at` DATETIME NULL AFTER `booking_id`,
    ADD COLUMN `reservation_expires_at` DATETIME NULL AFTER `reserved_at`,
    ADD COLUMN `used_at` DATETIME NULL AFTER `reservation_expires_at`,
    ADD COLUMN `released_at` DATETIME NULL AFTER `used_at`;

CREATE INDEX `booking_status`
    ON `loyalty_reward_vouchers` (`booking_id`, `status`);

CREATE INDEX `status_reservation_expires_at`
    ON `loyalty_reward_vouchers` (`status`, `reservation_expires_at`);

ALTER TABLE `bookings`
    ADD COLUMN `coupon_settled_at` DATETIME NULL AFTER `coupon_snapshot`;

UPDATE `bookings`
SET `coupon_settled_at` = COALESCE(`paid_at`, `updated_at`, `created_at`, NOW())
WHERE `payment_status` = 'paid'
  AND `coupon_id` IS NOT NULL
  AND `coupon_settled_at` IS NULL;
