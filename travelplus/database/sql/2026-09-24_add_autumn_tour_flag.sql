-- Import into the Travel Plus database before uploading the autumn collection update.
-- Existing tours remain unselected. Safe to import more than once.
SET @autumn_ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tours' AND COLUMN_NAME = 'is_autumn') = 0,
    'ALTER TABLE `tours` ADD COLUMN `is_autumn` TINYINT(1) NOT NULL DEFAULT 0',
    'SELECT 1'
);
PREPARE autumn_statement FROM @autumn_ddl;
EXECUTE autumn_statement;
DEALLOCATE PREPARE autumn_statement;
