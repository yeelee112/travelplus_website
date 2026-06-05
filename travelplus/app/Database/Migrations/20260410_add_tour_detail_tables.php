<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTourDetailTables extends Migration
{
    public function up()
    {
        $this->addTourColumns();
        $this->addTourTranslationColumns();

        $this->createTourMediaTable();
        $this->createTourItineraryDaysTable();
        $this->createTourItineraryDayTranslationsTable();
        $this->createTourInclusionsTable();
        $this->createTourInclusionTranslationsTable();
        $this->createTourFaqsTable();
        $this->createTourFaqTranslationsTable();
        $this->createTourReviewsTable();
        $this->createTourHighlightsTable();
        $this->createTourHighlightTranslationsTable();
    }

    public function down()
    {
        $this->forge->dropTable('tour_highlight_translations', true);
        $this->forge->dropTable('tour_highlights', true);
        $this->forge->dropTable('tour_reviews', true);
        $this->forge->dropTable('tour_faq_translations', true);
        $this->forge->dropTable('tour_faqs', true);
        $this->forge->dropTable('tour_inclusion_translations', true);
        $this->forge->dropTable('tour_inclusions', true);
        $this->forge->dropTable('tour_itinerary_day_translations', true);
        $this->forge->dropTable('tour_itinerary_days', true);
        $this->forge->dropTable('tour_media', true);

        $this->dropColumnsIfExist('tour_translations', [
            'overview',
            'booking_policy',
            'cancellation_policy',
            'price_note',
        ]);

        $this->dropColumnsIfExist('tours', [
            'sku',
            'code',
            'min_travelers',
            'max_travelers',
            'base_price',
            'sale_price',
            'child_price_rate',
            'infant_price_rate',
            'currency',
            'rating_avg',
            'reviews_count',
            'primary_destination_id',
            'map_embed',
        ]);
    }

    private function addTourColumns(): void
    {
        if (! $this->db->tableExists('tours')) {
            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('sku', 'tours')) {
            $fields['sku'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'id'];
        }

        if (! $this->db->fieldExists('code', 'tours')) {
            $fields['code'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'sku'];
        }

        if (! $this->db->fieldExists('min_travelers', 'tours')) {
            $fields['min_travelers'] = ['type' => 'INT', 'null' => true, 'after' => 'duration_nights'];
        }

        if (! $this->db->fieldExists('max_travelers', 'tours')) {
            $fields['max_travelers'] = ['type' => 'INT', 'null' => true, 'after' => 'min_travelers'];
        }

        if (! $this->db->fieldExists('base_price', 'tours')) {
            $fields['base_price'] = ['type' => 'INT', 'null' => true, 'after' => 'max_travelers'];
        }

        if (! $this->db->fieldExists('sale_price', 'tours')) {
            $fields['sale_price'] = ['type' => 'INT', 'null' => true, 'after' => 'base_price'];
        }

        if (! $this->db->fieldExists('child_price_rate', 'tours')) {
            $fields['child_price_rate'] = ['type' => 'DECIMAL', 'constraint' => '5,4', 'null' => false, 'default' => '0.8500', 'after' => 'sale_price'];
        }

        if (! $this->db->fieldExists('infant_price_rate', 'tours')) {
            $fields['infant_price_rate'] = ['type' => 'DECIMAL', 'constraint' => '5,4', 'null' => false, 'default' => '0.2500', 'after' => 'child_price_rate'];
        }

        if (! $this->db->fieldExists('currency', 'tours')) {
            $fields['currency'] = ['type' => 'VARCHAR', 'constraint' => 10, 'null' => false, 'default' => 'VND', 'after' => 'infant_price_rate'];
        }

        if (! $this->db->fieldExists('rating_avg', 'tours')) {
            $fields['rating_avg'] = ['type' => 'DECIMAL', 'constraint' => '3,2', 'null' => true, 'after' => 'currency'];
        }

        if (! $this->db->fieldExists('reviews_count', 'tours')) {
            $fields['reviews_count'] = ['type' => 'INT', 'null' => false, 'default' => 0, 'after' => 'rating_avg'];
        }

        if (! $this->db->fieldExists('primary_destination_id', 'tours')) {
            $fields['primary_destination_id'] = ['type' => 'INT', 'null' => true, 'after' => 'departure_location_id'];
        }

        if (! $this->db->fieldExists('map_embed', 'tours')) {
            $fields['map_embed'] = ['type' => 'TEXT', 'null' => true, 'after' => 'thumbnail'];
        }

        if ($fields !== []) {
            $this->forge->addColumn('tours', $fields);
        }
    }

    private function addTourTranslationColumns(): void
    {
        if (! $this->db->tableExists('tour_translations')) {
            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('overview', 'tour_translations')) {
            $fields['overview'] = ['type' => 'LONGTEXT', 'null' => true, 'after' => 'short_description'];
        }

        if (! $this->db->fieldExists('booking_policy', 'tour_translations')) {
            $fields['booking_policy'] = ['type' => 'LONGTEXT', 'null' => true, 'after' => 'itinerary'];
        }

        if (! $this->db->fieldExists('cancellation_policy', 'tour_translations')) {
            $fields['cancellation_policy'] = ['type' => 'LONGTEXT', 'null' => true, 'after' => 'booking_policy'];
        }

        if (! $this->db->fieldExists('price_note', 'tour_translations')) {
            $fields['price_note'] = ['type' => 'TEXT', 'null' => true, 'after' => 'cancellation_policy'];
        }

        if ($fields !== []) {
            $this->forge->addColumn('tour_translations', $fields);
        }
    }

    private function createTourMediaTable(): void
    {
        if ($this->db->tableExists('tour_media')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'tour_id' => ['type' => 'INT', 'null' => false],
            'type' => ['type' => 'ENUM', 'constraint' => ['cover', 'gallery', 'banner', 'video'], 'null' => false, 'default' => 'gallery'],
            'file_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'alt_text' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'sort_order' => ['type' => 'INT', 'null' => false, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tour_id');
        $this->forge->addForeignKey('tour_id', 'tours', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tour_media', true);
    }

    private function createTourItineraryDaysTable(): void
    {
        if ($this->db->tableExists('tour_itinerary_days')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'tour_id' => ['type' => 'INT', 'null' => false],
            'day_number' => ['type' => 'INT', 'null' => false],
            'meals' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'hotel_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'transport_summary' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'sort_order' => ['type' => 'INT', 'null' => false, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tour_id');
        $this->forge->addKey(['tour_id', 'day_number']);
        $this->forge->addForeignKey('tour_id', 'tours', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tour_itinerary_days', true);
    }

    private function createTourItineraryDayTranslationsTable(): void
    {
        if ($this->db->tableExists('tour_itinerary_day_translations')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'itinerary_day_id' => ['type' => 'INT', 'null' => false],
            'locale' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => false],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'description' => ['type' => 'LONGTEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['itinerary_day_id', 'locale'], false, true);
        $this->forge->addForeignKey('itinerary_day_id', 'tour_itinerary_days', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tour_itinerary_day_translations', true);
    }

    private function createTourInclusionsTable(): void
    {
        if ($this->db->tableExists('tour_inclusions')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'tour_id' => ['type' => 'INT', 'null' => false],
            'type' => ['type' => 'ENUM', 'constraint' => ['included', 'excluded'], 'null' => false, 'default' => 'included'],
            'icon' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'sort_order' => ['type' => 'INT', 'null' => false, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tour_id');
        $this->forge->addForeignKey('tour_id', 'tours', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tour_inclusions', true);
    }

    private function createTourInclusionTranslationsTable(): void
    {
        if ($this->db->tableExists('tour_inclusion_translations')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'tour_inclusion_id' => ['type' => 'INT', 'null' => false],
            'locale' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => false],
            'label' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tour_inclusion_id', 'locale'], false, true);
        $this->forge->addForeignKey('tour_inclusion_id', 'tour_inclusions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tour_inclusion_translations', true);
    }

    private function createTourFaqsTable(): void
    {
        if ($this->db->tableExists('tour_faqs')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'tour_id' => ['type' => 'INT', 'null' => false],
            'sort_order' => ['type' => 'INT', 'null' => false, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tour_id');
        $this->forge->addForeignKey('tour_id', 'tours', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tour_faqs', true);
    }

    private function createTourFaqTranslationsTable(): void
    {
        if ($this->db->tableExists('tour_faq_translations')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'faq_id' => ['type' => 'INT', 'null' => false],
            'locale' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => false],
            'question' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'answer' => ['type' => 'LONGTEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['faq_id', 'locale'], false, true);
        $this->forge->addForeignKey('faq_id', 'tour_faqs', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tour_faq_translations', true);
    }

    private function createTourReviewsTable(): void
    {
        if ($this->db->tableExists('tour_reviews')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'tour_id' => ['type' => 'INT', 'null' => false],
            'reviewer_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'reviewer_email' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'rating_overall' => ['type' => 'DECIMAL', 'constraint' => '3,2', 'null' => true],
            'rating_destination' => ['type' => 'DECIMAL', 'constraint' => '3,2', 'null' => true],
            'rating_transport' => ['type' => 'DECIMAL', 'constraint' => '3,2', 'null' => true],
            'rating_value' => ['type' => 'DECIMAL', 'constraint' => '3,2', 'null' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'content' => ['type' => 'LONGTEXT', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'hidden'], 'null' => false, 'default' => 'pending'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tour_id');
        $this->forge->addForeignKey('tour_id', 'tours', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tour_reviews', true);
    }

    private function createTourHighlightsTable(): void
    {
        if ($this->db->tableExists('tour_highlights')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'tour_id' => ['type' => 'INT', 'null' => false],
            'icon' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'sort_order' => ['type' => 'INT', 'null' => false, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tour_id');
        $this->forge->addForeignKey('tour_id', 'tours', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tour_highlights', true);
    }

    private function createTourHighlightTranslationsTable(): void
    {
        if ($this->db->tableExists('tour_highlight_translations')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'highlight_id' => ['type' => 'INT', 'null' => false],
            'locale' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => false],
            'label' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['highlight_id', 'locale'], false, true);
        $this->forge->addForeignKey('highlight_id', 'tour_highlights', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tour_highlight_translations', true);
    }

    private function dropColumnsIfExist(string $table, array $columns): void
    {
        if (! $this->db->tableExists($table)) {
            return;
        }

        foreach ($columns as $column) {
            if ($this->db->fieldExists($column, $table)) {
                $this->forge->dropColumn($table, $column);
            }
        }
    }
}
