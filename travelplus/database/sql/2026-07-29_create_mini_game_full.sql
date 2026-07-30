-- Mini Game "Đoán Biển Số - Đoán Tỉnh/Thành"
-- Import trực tiếp vào database travelplus_db bằng phpMyAdmin hoặc HeidiSQL.
-- Script có thể chạy lại: dữ liệu game cũ sẽ bị xóa và tạo mới.
-- Bộ 40 câu sử dụng tên tỉnh/thành và biển số trước đợt sáp nhập năm 2025.
-- Thứ tự câu hỏi đã được xáo trộn, không sắp xếp tăng dần theo biển số.

CREATE DATABASE IF NOT EXISTS `travelplus_db`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `travelplus_db`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `game_buzzes`;
DROP TABLE IF EXISTS `game_state`;
DROP TABLE IF EXISTS `game_players`;
DROP TABLE IF EXISTS `game_questions`;

CREATE TABLE `game_questions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `plate_code` VARCHAR(20) NOT NULL,
  `province` VARCHAR(120) NOT NULL,
  `places` TEXT NULL,
  `specialty` VARCHAR(255) NULL,
  `airport` VARCHAR(255) NULL,
  `unesco` VARCHAR(255) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_game_questions_active_sort` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `game_players` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `office` VARCHAR(60) NOT NULL,
  `token` CHAR(64) NOT NULL,
  `score` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_game_players_token` (`token`),
  KEY `idx_game_players_office` (`office`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `game_state` (
  `id` TINYINT UNSIGNED NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'waiting',
  `question_id` INT UNSIGNED NULL,
  `question_number` INT NOT NULL DEFAULT 0,
  `question_type` VARCHAR(30) NOT NULL DEFAULT 'plate_to_province',
  `answer_revealed` TINYINT(1) NOT NULL DEFAULT 0,
  `buzz_open` TINYINT(1) NOT NULL DEFAULT 0,
  `countdown_seconds` INT NOT NULL DEFAULT 20,
  `countdown_ends_at` DATETIME NULL,
  `main_points` INT NOT NULL DEFAULT 2,
  `bonus_points` INT NOT NULL DEFAULT 1,
  `bonus_type` VARCHAR(20) NULL,
  `bonus_active` TINYINT(1) NOT NULL DEFAULT 0,
  `version` BIGINT UNSIGNED NOT NULL DEFAULT 1,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_game_state_question` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `game_buzzes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `question_id` INT UNSIGNED NOT NULL,
  `round_no` INT NOT NULL DEFAULT 1,
  `player_id` INT UNSIGNED NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'waiting',
  `buzzed_at` DATETIME(6) NOT NULL,
  `buzz_order` INT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_game_buzz_player_round` (`question_id`, `round_no`, `player_id`),
  KEY `idx_game_buzz_order` (`question_id`, `round_no`, `buzz_order`),
  KEY `idx_game_buzz_player` (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `game_state` (
  `id`, `status`, `question_id`, `question_number`, `question_type`, `answer_revealed`,
  `buzz_open`, `countdown_seconds`, `main_points`, `bonus_points`,
  `bonus_active`, `version`, `updated_at`
) VALUES (1, 'waiting', NULL, 0, 'plate_to_province', 0, 0, 20, 2, 1, 0, 1, NOW());

INSERT INTO `game_questions`
(`plate_code`, `province`, `places`, `specialty`, `airport`, `unesco`, `sort_order`, `is_active`, `created_at`, `updated_at`)
VALUES
('75','Thừa Thiên Huế','Đại Nội\nChùa Thiên Mụ\nLăng Khải Định','Bún bò Huế','Phú Bài','Quần thể Di tích Cố đô Huế',1,1,NOW(),NOW()),
('47','Đắk Lắk','Buôn Đôn\nHồ Lắk\nBảo tàng Thế giới Cà phê','Cà phê Buôn Ma Thuột','Buôn Ma Thuột',NULL,2,1,NOW(),NOW()),
('15–16','Hải Phòng','Vịnh Lan Hạ\nĐảo Cát Bà\nBãi biển Đồ Sơn','Bánh đa cua','Cát Bi','Vịnh Hạ Long – Quần đảo Cát Bà',3,1,NOW(),NOW()),
('68','Kiên Giang','Phú Quốc\nHà Tiên\nQuần đảo Nam Du','Gỏi cá trích','Phú Quốc',NULL,4,1,NOW(),NOW()),
('29–33, 40','Hà Nội','Hồ Hoàn Kiếm\nVăn Miếu\nHoàng thành Thăng Long','Phở Hà Nội','Nội Bài','Khu trung tâm Hoàng thành Thăng Long',5,1,NOW(),NOW()),
('79','Khánh Hòa','Vịnh Nha Trang\nTháp Bà Ponagar\nHòn Mun','Bún chả cá','Cam Ranh',NULL,6,1,NOW(),NOW()),
('11','Cao Bằng','Thác Bản Giốc\nKhu di tích Pác Bó\nĐộng Ngườm Ngao','Bánh cuốn Cao Bằng',NULL,'Công viên địa chất Non nước Cao Bằng',7,1,NOW(),NOW()),
('43','Đà Nẵng','Bà Nà Hills\nNgũ Hành Sơn\nBán đảo Sơn Trà','Mì Quảng','Đà Nẵng',NULL,8,1,NOW(),NOW()),
('65','Cần Thơ','Bến Ninh Kiều\nChợ nổi Cái Răng\nNhà cổ Bình Thủy','Bánh cống','Cần Thơ',NULL,9,1,NOW(),NOW()),
('14','Quảng Ninh','Vịnh Hạ Long\nYên Tử\nĐảo Cô Tô','Chả mực Hạ Long','Vân Đồn','Vịnh Hạ Long',10,1,NOW(),NOW()),
('49','Lâm Đồng','Hồ Xuân Hương\nThung lũng Tình Yêu\nNúi Langbiang','Bánh căn Đà Lạt','Liên Khương','Không gian văn hóa Cồng chiêng Tây Nguyên',11,1,NOW(),NOW()),
('51, 59','TP. Hồ Chí Minh','Dinh Độc Lập\nChợ Bến Thành\nĐịa đạo Củ Chi','Cơm tấm Sài Gòn','Tân Sơn Nhất',NULL,12,1,NOW(),NOW()),
('37','Nghệ An','Làng Sen quê Bác\nBiển Cửa Lò\nVườn quốc gia Pù Mát','Cháo lươn Vinh','Vinh',NULL,13,1,NOW(),NOW()),
('92','Quảng Nam','Phố cổ Hội An\nThánh địa Mỹ Sơn\nCù Lao Chàm','Cao lầu Hội An','Chu Lai','Phố cổ Hội An',14,1,NOW(),NOW()),
('78','Phú Yên','Gành Đá Đĩa\nBãi Xép\nTháp Nhạn','Mắt cá ngừ đại dương','Tuy Hòa',NULL,15,1,NOW(),NOW()),
('72','Bà Rịa – Vũng Tàu','Bãi Sau Vũng Tàu\nHồ Tràm\nTượng Chúa Kitô Vua','Bánh khọt Vũng Tàu','Côn Đảo',NULL,16,1,NOW(),NOW()),
('76','Quảng Ngãi','Đảo Lý Sơn\nBiển Mỹ Khê\nNúi Thiên Ấn','Don Quảng Ngãi','Chu Lai',NULL,17,1,NOW(),NOW()),
('74','Quảng Trị','Thành cổ Quảng Trị\nĐịa đạo Vịnh Mốc\nCầu Hiền Lương','Bún hến Mai Xá',NULL,NULL,18,1,NOW(),NOW()),
('73','Quảng Bình','Động Phong Nha\nHang Sơn Đoòng\nBiển Nhật Lệ','Cháo canh Quảng Bình','Đồng Hới','Vườn quốc gia Phong Nha – Kẻ Bàng',19,1,NOW(),NOW()),
('36','Thanh Hóa','Thành Nhà Hồ\nBiển Sầm Sơn\nPù Luông','Nem chua Thanh Hóa','Thọ Xuân','Thành Nhà Hồ',20,1,NOW(),NOW()),
('18','Nam Định','Đền Trần\nNhà thờ Phú Nhai\nBiển Thịnh Long','Phở bò Nam Định',NULL,NULL,21,1,NOW(),NOW()),
('35','Ninh Bình','Quần thể Tràng An\nTam Cốc – Bích Động\nChùa Bái Đính','Cơm cháy Ninh Bình',NULL,'Quần thể danh thắng Tràng An',22,1,NOW(),NOW()),
('17','Thái Bình','Chùa Keo\nBiển Đồng Châu\nCồn Vành','Canh cá Quỳnh Côi',NULL,NULL,23,1,NOW(),NOW()),
('89','Hưng Yên','Phố Hiến\nChùa Chuông\nĐền Mẫu','Nhãn lồng Hưng Yên',NULL,NULL,24,1,NOW(),NOW()),
('98','Bắc Giang','Chùa Vĩnh Nghiêm\nTây Yên Tử\nHồ Cấm Sơn','Vải thiều Lục Ngạn',NULL,'Mộc bản chùa Vĩnh Nghiêm',25,1,NOW(),NOW()),
('99','Bắc Ninh','Chùa Dâu\nĐền Đô\nLàng tranh Đông Hồ','Bánh phu thê',NULL,'Dân ca Quan họ Bắc Ninh',26,1,NOW(),NOW()),
('20','Thái Nguyên','Hồ Núi Cốc\nATK Định Hóa\nBảo tàng Văn hóa các dân tộc Việt Nam','Chè Tân Cương',NULL,NULL,27,1,NOW(),NOW()),
('97','Bắc Kạn','Hồ Ba Bể\nĐộng Hua Mạ\nThác Đầu Đẳng','Miến dong Bắc Kạn',NULL,NULL,28,1,NOW(),NOW()),
('21','Yên Bái','Ruộng bậc thang Mù Cang Chải\nHồ Thác Bà\nSuối Giàng','Cốm Tú Lệ',NULL,NULL,29,1,NOW(),NOW()),
('24','Lào Cai','Sa Pa\nĐỉnh Fansipan\nĐèo Ô Quy Hồ','Thắng cố','Sa Pa',NULL,30,1,NOW(),NOW()),
('22','Tuyên Quang','Khu di tích Tân Trào\nNa Hang\nSuối khoáng Mỹ Lâm','Cam sành Hàm Yên',NULL,NULL,31,1,NOW(),NOW()),
('23','Hà Giang','Cột cờ Lũng Cú\nHẻm Tu Sản\nĐèo Mã Pí Lèng','Cháo ấu tẩu',NULL,'Công viên địa chất Cao nguyên đá Đồng Văn',32,1,NOW(),NOW()),
('19','Phú Thọ','Đền Hùng\nĐồi chè Long Cốc\nVườn quốc gia Xuân Sơn','Thịt chua Thanh Sơn',NULL,'Tín ngưỡng thờ cúng Hùng Vương',33,1,NOW(),NOW()),
('28','Hòa Bình','Hồ Hòa Bình\nMai Châu\nThung Nai','Cơm lam Hòa Bình',NULL,'Mo Mường Hòa Bình',34,1,NOW(),NOW()),
('88','Vĩnh Phúc','Tam Đảo\nThiền viện Trúc Lâm Tây Thiên\nHồ Đại Lải','Su su Tam Đảo',NULL,NULL,35,1,NOW(),NOW()),
('67','An Giang','Miếu Bà Chúa Xứ Núi Sam\nRừng tràm Trà Sư\nNúi Cấm','Bún cá An Giang',NULL,NULL,36,1,NOW(),NOW()),
('94','Bạc Liêu','Nhà Công tử Bạc Liêu\nCánh đồng điện gió\nChùa Xiêm Cán','Bún bò cay Bạc Liêu',NULL,'Nghệ thuật Đờn ca tài tử Nam Bộ',37,1,NOW(),NOW()),
('69','Cà Mau','Mũi Cà Mau\nRừng U Minh Hạ\nHòn Đá Bạc','Cua Cà Mau','Cà Mau',NULL,38,1,NOW(),NOW()),
('83','Sóc Trăng','Chùa Dơi\nChùa Chén Kiểu\nChợ nổi Ngã Năm','Bánh pía Sóc Trăng',NULL,NULL,39,1,NOW(),NOW()),
('95','Hậu Giang','Khu bảo tồn Lung Ngọc Hoàng\nChợ nổi Ngã Bảy\nCông viên giải trí Kittyd & Minnied','Khóm Cầu Đúc',NULL,NULL,40,1,NOW(),NOW());

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Mini game đã được khởi tạo thành công' AS `message`;
SELECT COUNT(*) AS `question_count` FROM `game_questions`;
