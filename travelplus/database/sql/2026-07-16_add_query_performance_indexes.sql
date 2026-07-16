-- Add composite indexes for the queries used by public tour pages, booking lookup,
-- CRM, booking emails and analytics reports.
-- Safe to run more than once from phpMyAdmin. No migration command is required.

SET @schema_name := DATABASE();

SET @sql := (
  SELECT IF(
    EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'bookings')
      AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'bookings' AND INDEX_NAME = 'idx_bookings_customer_email_created'),
    'ALTER TABLE `bookings` ADD INDEX `idx_bookings_customer_email_created` (`customer_email`, `created_at`)',
    'SELECT ''bookings email lookup index already exists or table is missing'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'bookings')
      AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'bookings' AND INDEX_NAME = 'idx_bookings_customer_phone_created'),
    'ALTER TABLE `bookings` ADD INDEX `idx_bookings_customer_phone_created` (`customer_phone`, `created_at`)',
    'SELECT ''bookings phone lookup index already exists or table is missing'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'bookings')
      AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'bookings' AND INDEX_NAME = 'idx_bookings_status_method_created'),
    'ALTER TABLE `bookings` ADD INDEX `idx_bookings_status_method_created` (`payment_status`, `payment_method`, `created_at`)',
    'SELECT ''bookings reconciliation index already exists or table is missing'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'crm_leads')
      AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'crm_leads' AND INDEX_NAME = 'idx_crm_leads_stage_source_updated'),
    'ALTER TABLE `crm_leads` ADD INDEX `idx_crm_leads_stage_source_updated` (`stage`, `source`, `updated_at`, `created_at`)',
    'SELECT ''CRM stage/source index already exists or table is missing'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'crm_leads')
      AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'crm_leads' AND INDEX_NAME = 'idx_crm_leads_source_updated'),
    'ALTER TABLE `crm_leads` ADD INDEX `idx_crm_leads_source_updated` (`source`, `updated_at`, `created_at`)',
    'SELECT ''CRM source index already exists or table is missing'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'tour_departures')
      AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'tour_departures' AND INDEX_NAME = 'idx_tour_departures_lookup'),
    'ALTER TABLE `tour_departures` ADD INDEX `idx_tour_departures_lookup` (`tour_id`, `status`, `departure_date`)',
    'SELECT ''tour departure lookup index already exists or table is missing'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'tour_media')
      AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'tour_media' AND INDEX_NAME = 'idx_tour_media_lookup'),
    'ALTER TABLE `tour_media` ADD INDEX `idx_tour_media_lookup` (`tour_id`, `type`, `sort_order`)',
    'SELECT ''tour media lookup index already exists or table is missing'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'tour_reviews')
      AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'tour_reviews' AND INDEX_NAME = 'idx_tour_reviews_public'),
    'ALTER TABLE `tour_reviews` ADD INDEX `idx_tour_reviews_public` (`tour_id`, `status`, `created_at`)',
    'SELECT ''tour review index already exists or table is missing'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'tour_translations')
      AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'tour_translations' AND INDEX_NAME = 'idx_tour_translations_locale_slug'),
    'ALTER TABLE `tour_translations` ADD INDEX `idx_tour_translations_locale_slug` (`locale`, `slug`)',
    'SELECT ''tour translation slug index already exists or table is missing'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'location_translations')
      AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'location_translations' AND INDEX_NAME = 'idx_location_translations_locale_slug'),
    'ALTER TABLE `location_translations` ADD INDEX `idx_location_translations_locale_slug` (`locale`, `slug`)',
    'SELECT ''location translation slug index already exists or table is missing'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'tours')
      AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'tours' AND INDEX_NAME = 'idx_tours_catalog'),
    'ALTER TABLE `tours` ADD INDEX `idx_tours_catalog` (`status`, `tour_type`, `created_at`)',
    'SELECT ''tour catalog index already exists or table is missing'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'booking_email_logs')
      AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'booking_email_logs' AND INDEX_NAME = 'idx_booking_email_logs_dedupe'),
    'ALTER TABLE `booking_email_logs` ADD INDEX `idx_booking_email_logs_dedupe` (`booking_id`, `email_type`, `status`, `recipient_email`)',
    'SELECT ''booking email dedupe index already exists or table is missing'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'booking_status_logs')
      AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'booking_status_logs' AND INDEX_NAME = 'idx_booking_status_logs_timeline'),
    'ALTER TABLE `booking_status_logs` ADD INDEX `idx_booking_status_logs_timeline` (`booking_id`, `created_at`)',
    'SELECT ''booking status timeline index already exists or table is missing'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'analytics_page_views')
      AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'analytics_page_views' AND INDEX_NAME = 'idx_analytics_page_views_journey'),
    'ALTER TABLE `analytics_page_views` ADD INDEX `idx_analytics_page_views_journey` (`visit_id`, `viewed_at`)',
    'SELECT ''analytics page journey index already exists or table is missing'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'analytics_search_queries')
      AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'analytics_search_queries' AND INDEX_NAME = 'idx_analytics_search_queries_journey'),
    'ALTER TABLE `analytics_search_queries` ADD INDEX `idx_analytics_search_queries_journey` (`visit_id`, `searched_at`)',
    'SELECT ''analytics search journey index already exists or table is missing'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
