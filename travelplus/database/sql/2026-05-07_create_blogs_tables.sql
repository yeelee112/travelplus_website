CREATE TABLE IF NOT EXISTS `blogs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category` VARCHAR(120) NOT NULL DEFAULT 'Cảm hứng du lịch',
  `author_name` VARCHAR(120) NOT NULL DEFAULT 'Travel Plus',
  `thumbnail` VARCHAR(255) NOT NULL,
  `cover_image` VARCHAR(255) DEFAULT NULL,
  `featured_image` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('draft','published') NOT NULL DEFAULT 'published',
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `published_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_blogs_status_published` (`status`, `published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_translations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `blog_id` INT UNSIGNED NOT NULL,
  `locale` VARCHAR(5) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `excerpt` TEXT DEFAULT NULL,
  `content` LONGTEXT DEFAULT NULL,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_blog_translation_locale_slug` (`locale`, `slug`),
  UNIQUE KEY `uniq_blog_translation_blog_locale` (`blog_id`, `locale`),
  CONSTRAINT `fk_blog_translations_blog`
    FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `blogs` (`id`, `category`, `author_name`, `thumbnail`, `cover_image`, `featured_image`, `status`, `is_featured`, `published_at`)
VALUES
  (1, 'Cảm hứng du lịch', 'Travel Plus', 'assets/images/home/banner02.jpg', 'assets/images/home/banner02.jpg', 'assets/images/home/banner02.jpg', 'published', 1, '2026-05-01 09:00:00'),
  (2, 'Kinh nghiệm du lịch', 'Travel Plus', 'assets/images/home/banner03.webp', 'assets/images/home/banner03.webp', 'assets/images/home/banner03.webp', 'published', 0, '2026-04-25 09:00:00'),
  (3, 'Điểm đến nổi bật', 'Travel Plus', 'assets/images/home/banner01.jpg', 'assets/images/home/banner01.jpg', 'assets/images/home/banner01.jpg', 'published', 0, '2026-04-20 09:00:00')
ON DUPLICATE KEY UPDATE
  `category` = VALUES(`category`),
  `author_name` = VALUES(`author_name`),
  `thumbnail` = VALUES(`thumbnail`),
  `cover_image` = VALUES(`cover_image`),
  `featured_image` = VALUES(`featured_image`),
  `status` = VALUES(`status`),
  `is_featured` = VALUES(`is_featured`),
  `published_at` = VALUES(`published_at`);

INSERT INTO `blog_translations` (`blog_id`, `locale`, `title`, `slug`, `excerpt`, `content`, `meta_title`, `meta_description`)
VALUES
  (
    1,
    'vi',
    'Top 10 bãi biển đáng đến nhất trong mùa hè này',
    'top-10-bai-bien-dang-den-nhat-trong-mua-he-nay',
    'Gợi ý những bãi biển nổi bật cho mùa hè, từ Maldives đến Phuket, phù hợp cho nghỉ dưỡng, trải nghiệm và chụp ảnh.',
    '<p>Mùa hè là thời điểm lý tưởng để lên kế hoạch cho một chuyến đi biển trọn vẹn. Từ những vùng nước trong xanh, bãi cát dài đến các hoạt động thư giãn và khám phá, mỗi điểm đến đều mang một màu sắc riêng.</p><p><strong>Maldives</strong> phù hợp cho kỳ nghỉ dưỡng riêng tư, <strong>Maui</strong> nổi bật với thiên nhiên và trải nghiệm ngoài trời, còn <strong>Phuket</strong> phù hợp cho nhóm bạn hoặc gia đình muốn kết hợp nghỉ dưỡng và vui chơi.</p><p>Khi lên kế hoạch, bạn nên cân nhắc thời gian bay, ngân sách, mùa đẹp nhất và nhu cầu thực tế của đoàn để chọn hành trình phù hợp.</p>',
    'Top 10 bãi biển đáng đến nhất trong mùa hè này | Travel Plus',
    'Khám phá các bãi biển đẹp cho mùa hè cùng Travel Plus với gợi ý điểm đến, trải nghiệm và mẹo lên kế hoạch.'
  ),
  (
    1,
    'en',
    'Top 10 Beaches to Visit This Summer Season',
    'top-10-beaches-to-visit-this-summer-season',
    'Explore standout beach destinations for summer, from the Maldives to Phuket, with practical planning ideas.',
    '<p>Summer is the ideal time to plan a beach escape. From clear blue water and long sandy shores to light adventure and relaxation, each destination offers a different rhythm.</p><p><strong>Maldives</strong> is ideal for private luxury stays, <strong>Maui</strong> fits nature-driven travelers, and <strong>Phuket</strong> works well for families and groups wanting a balance of beach time and activities.</p><p>When building your plan, consider flight duration, budget, best travel season and the actual needs of your group.</p>',
    'Top 10 Beaches to Visit This Summer Season | Travel Plus',
    'Discover inspiring summer beach destinations with Travel Plus, including practical travel planning ideas and highlights.'
  ),
  (
    2,
    'vi',
    'Kinh nghiệm lên lịch trình châu Âu lần đầu',
    'kinh-nghiem-len-lich-trinh-chau-au-lan-dau',
    'Những lưu ý quan trọng khi đi châu Âu lần đầu: chọn điểm đến, nhịp di chuyển, visa và ngân sách.',
    '<p>Nếu đây là lần đầu bạn đi châu Âu, hãy ưu tiên lịch trình vừa phải, tập trung vào một cụm quốc gia để tiết kiệm thời gian di chuyển. Một hành trình phổ biến là <strong>Pháp - Thụy Sĩ - Ý</strong> hoặc <strong>Đức - Áo - Séc</strong>.</p><p>Ngoài visa, bạn cần tính kỹ thời gian di chuyển giữa các thành phố, chọn mùa phù hợp và chuẩn bị ngân sách cho vé tàu, khách sạn và bảo hiểm.</p>',
    'Kinh nghiệm lên lịch trình châu Âu lần đầu | Travel Plus',
    'Tổng hợp kinh nghiệm đi châu Âu lần đầu: chọn tuyến, nhịp lịch trình, visa và dự toán chi phí.'
  ),
  (
    2,
    'en',
    'How to Plan Your First Europe Itinerary',
    'how-to-plan-your-first-europe-itinerary',
    'Key planning notes for a first Europe trip: route, pace, visa preparation and budget control.',
    '<p>If this is your first Europe trip, keep the route focused and realistic. Grouping countries within the same region helps reduce transfer time and keeps the journey comfortable.</p><p>Common first-trip combinations include <strong>France - Switzerland - Italy</strong> or <strong>Germany - Austria - Czech Republic</strong>. Visa timing, hotel locations and rail connections should be planned early.</p>',
    'How to Plan Your First Europe Itinerary | Travel Plus',
    'Practical advice for planning a first Europe itinerary, including route choice, pace, visa timing and budget.'
  ),
  (
    3,
    'vi',
    'Những điểm check-in nổi bật cho hành trình Nhật Bản',
    'nhung-diem-check-in-noi-bat-cho-hanh-trinh-nhat-ban',
    'Danh sách điểm check-in nổi bật nếu bạn muốn kết hợp văn hóa, ẩm thực và cảnh quan khi đi Nhật Bản.',
    '<p>Với hành trình Nhật Bản, bạn có thể kết hợp <strong>Tokyo</strong>, <strong>Kyoto</strong> và <strong>Osaka</strong> để trải nghiệm đủ nhịp hiện đại, truyền thống và ẩm thực đặc trưng.</p><p>Nếu đi theo mùa hoa anh đào hoặc mùa lá đỏ, hãy đặt dịch vụ sớm vì giá vé máy bay và khách sạn thường tăng nhanh.</p>',
    'Những điểm check-in nổi bật cho hành trình Nhật Bản | Travel Plus',
    'Gợi ý các điểm check-in đẹp và hợp lịch trình khi du lịch Nhật Bản cùng Travel Plus.'
  ),
  (
    3,
    'en',
    'Best Photo Spots for a Japan Itinerary',
    'best-photo-spots-for-a-japan-itinerary',
    'Recommended photo spots and city combinations for a well-balanced Japan trip.',
    '<p>A balanced Japan itinerary can combine <strong>Tokyo</strong>, <strong>Kyoto</strong> and <strong>Osaka</strong> for modern city energy, traditional heritage and food culture.</p><p>If you travel during cherry blossom or autumn foliage season, book flights and hotels early because prices move quickly.</p>',
    'Best Photo Spots for a Japan Itinerary | Travel Plus',
    'Discover standout photo spots and route ideas for a Japan itinerary with Travel Plus.'
  )
ON DUPLICATE KEY UPDATE
  `title` = VALUES(`title`),
  `slug` = VALUES(`slug`),
  `excerpt` = VALUES(`excerpt`),
  `content` = VALUES(`content`),
  `meta_title` = VALUES(`meta_title`),
  `meta_description` = VALUES(`meta_description`);
