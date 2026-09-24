-- Run once before deploying the collection-based tour admin. Can be re-imported.
CREATE TABLE IF NOT EXISTS tour_collections (
 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 slug VARCHAR(100) NOT NULL UNIQUE,
 name_vi VARCHAR(150) NOT NULL,
 name_en VARCHAR(150) NOT NULL DEFAULT '',
 is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS tour_collection_tours (
 collection_id INT NOT NULL,
 tour_id INT NOT NULL,
 PRIMARY KEY (collection_id,tour_id),
 KEY idx_collection_tour (tour_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO tour_collections (slug,name_vi,name_en,is_active)
SELECT 'mua-thu','Mùa thu','Autumn',1
WHERE NOT EXISTS (SELECT 1 FROM tour_collections WHERE slug='mua-thu');
SET @has_autumn = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tours' AND COLUMN_NAME='is_autumn');
SET @collection_copy = IF(@has_autumn > 0,
 'INSERT IGNORE INTO tour_collection_tours (collection_id,tour_id) SELECT c.id,t.id FROM tour_collections c CROSS JOIN tours t WHERE c.slug=''mua-thu'' AND t.is_autumn=1', 'SELECT 1');
PREPARE collection_stmt FROM @collection_copy;
EXECUTE collection_stmt;
DEALLOCATE PREPARE collection_stmt;
SET @collection_drop = IF(@has_autumn > 0,'ALTER TABLE tours DROP COLUMN is_autumn','SELECT 1');
PREPARE collection_stmt FROM @collection_drop;
EXECUTE collection_stmt;
DEALLOCATE PREPARE collection_stmt;
