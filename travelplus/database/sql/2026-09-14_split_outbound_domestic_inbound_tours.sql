-- Tách rõ ba loại tour:
-- outbound: khách Việt đi nước ngoài
-- domestic: khách Việt đi trong nước
-- inbound: khách quốc tế đi Việt Nam, Đông Dương và các nước lân cận

ALTER TABLE `tour_categories`
    MODIFY COLUMN `type` ENUM('outbound', 'domestic', 'inbound')
    COLLATE utf8mb4_unicode_520_ci NOT NULL;

ALTER TABLE `tours`
    MODIFY COLUMN `tour_type` ENUM('outbound', 'domestic', 'inbound')
    COLLATE utf8mb4_unicode_520_ci NOT NULL;

-- Dữ liệu cũ đang dùng inbound để biểu diễn tour trong nước.
-- Chỉ đổi các danh mục có slug domestic, nên chạy lại file cũng không đổi nhầm inbound mới.
UPDATE `tours` t
INNER JOIN `tour_categories` tc ON tc.id = t.category_id
INNER JOIN `tour_category_translations` tct ON tct.category_id = tc.id
SET t.`tour_type` = 'domestic'
WHERE t.`tour_type` = 'inbound'
  AND tc.`type` = 'inbound'
  AND tct.`slug` IN ('tour-trong-nuoc', 'domestic-tours');

UPDATE `tour_categories` tc
INNER JOIN `tour_category_translations` tct ON tct.category_id = tc.id
SET tc.`type` = 'domestic'
WHERE tc.`type` = 'inbound'
  AND tct.`slug` IN ('tour-trong-nuoc', 'domestic-tours');

INSERT INTO `tour_categories` (`parent_id`, `type`, `created_at`, `updated_at`)
SELECT NULL, 'inbound', NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `tour_categories` WHERE `type` = 'inbound'
);

SET @inbound_category_id := (
    SELECT `id`
    FROM `tour_categories`
    WHERE `type` = 'inbound'
    ORDER BY `id`
    LIMIT 1
);

INSERT INTO `tour_category_translations` (`category_id`, `locale`, `name`, `slug`)
SELECT @inbound_category_id, 'vi', 'Tour inbound & Đông Dương', 'tour-inbound'
WHERE @inbound_category_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `tour_category_translations`
      WHERE `category_id` = @inbound_category_id AND `locale` = 'vi'
  );

INSERT INTO `tour_category_translations` (`category_id`, `locale`, `name`, `slug`)
SELECT @inbound_category_id, 'en', 'Inbound & Indochina Tours', 'inbound-tours'
WHERE @inbound_category_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `tour_category_translations`
      WHERE `category_id` = @inbound_category_id AND `locale` = 'en'
  );

-- Đồng bộ lại tên nếu file đã từng được chạy ở phiên bản chỉ ghi phạm vi Việt Nam.
UPDATE `tour_category_translations`
SET `name` = 'Tour inbound & Đông Dương', `slug` = 'tour-inbound'
WHERE `category_id` = @inbound_category_id AND `locale` = 'vi';

UPDATE `tour_category_translations`
SET `name` = 'Inbound & Indochina Tours', `slug` = 'inbound-tours'
WHERE `category_id` = @inbound_category_id AND `locale` = 'en';

SELECT
    tc.id,
    tc.type,
    MAX(CASE WHEN tct.locale = 'vi' THEN tct.name END) AS name_vi,
    MAX(CASE WHEN tct.locale = 'en' THEN tct.name END) AS name_en,
    COUNT(t.id) AS tour_count
FROM tour_categories tc
LEFT JOIN tour_category_translations tct ON tct.category_id = tc.id
LEFT JOIN tours t ON t.category_id = tc.id
GROUP BY tc.id, tc.type
ORDER BY FIELD(tc.type, 'outbound', 'domestic', 'inbound'), tc.id;
