-- Chỉ dùng file này nếu bạn đã import bản mini game cũ và muốn nâng cấp,
-- không muốn chạy lại file create_mini_game_full.sql.
USE `travelplus_db`;

ALTER TABLE `game_state`
  ADD COLUMN `question_type` VARCHAR(30) NOT NULL DEFAULT 'plate_to_province'
  AFTER `question_number`;

UPDATE `game_state`
SET `question_type` = 'plate_to_province', `version` = `version` + 1
WHERE `id` = 1;
