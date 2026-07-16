<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddQueryPerformanceIndexes extends Migration
{
    /**
     * @var array<string, array<string, string>>
     */
    private const INDEXES = [
        'bookings' => [
            'idx_bookings_customer_email_created' => '(`customer_email`, `created_at`)',
            'idx_bookings_customer_phone_created' => '(`customer_phone`, `created_at`)',
            'idx_bookings_status_method_created' => '(`payment_status`, `payment_method`, `created_at`)',
        ],
        'crm_leads' => [
            'idx_crm_leads_stage_source_updated' => '(`stage`, `source`, `updated_at`, `created_at`)',
            'idx_crm_leads_source_updated' => '(`source`, `updated_at`, `created_at`)',
        ],
        'tour_departures' => [
            'idx_tour_departures_lookup' => '(`tour_id`, `status`, `departure_date`)',
        ],
        'tour_media' => [
            'idx_tour_media_lookup' => '(`tour_id`, `type`, `sort_order`)',
        ],
        'tour_reviews' => [
            'idx_tour_reviews_public' => '(`tour_id`, `status`, `created_at`)',
        ],
        'tour_translations' => [
            'idx_tour_translations_locale_slug' => '(`locale`, `slug`)',
        ],
        'location_translations' => [
            'idx_location_translations_locale_slug' => '(`locale`, `slug`)',
        ],
        'tours' => [
            'idx_tours_catalog' => '(`status`, `tour_type`, `created_at`)',
        ],
        'booking_email_logs' => [
            'idx_booking_email_logs_dedupe' => '(`booking_id`, `email_type`, `status`, `recipient_email`)',
        ],
        'booking_status_logs' => [
            'idx_booking_status_logs_timeline' => '(`booking_id`, `created_at`)',
        ],
        'analytics_page_views' => [
            'idx_analytics_page_views_journey' => '(`visit_id`, `viewed_at`)',
        ],
        'analytics_search_queries' => [
            'idx_analytics_search_queries_journey' => '(`visit_id`, `searched_at`)',
        ],
    ];

    public function up()
    {
        foreach (self::INDEXES as $table => $indexes) {
            if (! $this->db->tableExists($table)) {
                continue;
            }

            foreach ($indexes as $name => $columns) {
                if ($this->indexExists($table, $name)) {
                    continue;
                }

                $this->db->query("ALTER TABLE `{$table}` ADD INDEX `{$name}` {$columns}");
            }
        }
    }

    public function down()
    {
        foreach (array_reverse(self::INDEXES, true) as $table => $indexes) {
            if (! $this->db->tableExists($table)) {
                continue;
            }

            foreach (array_reverse($indexes, true) as $name => $columns) {
                if (! $this->indexExists($table, $name)) {
                    continue;
                }

                $this->db->query("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
            }
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        return $this->db->query(
            'SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1',
            [$table, $index]
        )->getRowArray() !== null;
    }
}
