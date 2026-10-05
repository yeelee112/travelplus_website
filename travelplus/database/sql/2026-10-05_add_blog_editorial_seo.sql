-- Run on the same database as the website before deploying the SEO fields.
SET @seo_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='blog_translations' AND COLUMN_NAME='focus_keyword');
SET @seo_sql = IF(@seo_exists = 0, 'ALTER TABLE blog_translations ADD COLUMN focus_keyword TEXT NULL', 'SELECT 1');
PREPARE seo_stmt FROM @seo_sql;
EXECUTE seo_stmt;
DEALLOCATE PREPARE seo_stmt;
SET @seo_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='blog_translations' AND COLUMN_NAME='secondary_keywords');
SET @seo_sql = IF(@seo_exists = 0, 'ALTER TABLE blog_translations ADD COLUMN secondary_keywords TEXT NULL', 'SELECT 1');
PREPARE seo_stmt FROM @seo_sql;
EXECUTE seo_stmt;
DEALLOCATE PREPARE seo_stmt;
SET @seo_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='blog_translations' AND COLUMN_NAME='social_hashtags');
SET @seo_sql = IF(@seo_exists = 0, 'ALTER TABLE blog_translations ADD COLUMN social_hashtags TEXT NULL', 'SELECT 1');
PREPARE seo_stmt FROM @seo_sql;
EXECUTE seo_stmt;
DEALLOCATE PREPARE seo_stmt;
