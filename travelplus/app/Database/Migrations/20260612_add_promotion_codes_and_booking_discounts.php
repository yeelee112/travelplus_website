<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPromotionCodesAndBookingDiscounts extends Migration
{
    public function up()
    {
        $this->createPromotionCodesTable();
        $this->createPromotionCodeToursTable();
        $this->addBookingDiscountColumns();
    }

    public function down()
    {
        $this->forge->dropTable('promotion_code_tours', true);

        if ($this->db->tableExists('bookings')) {
            foreach (['coupon_snapshot', 'coupon_code', 'coupon_id', 'discount_amount_vnd', 'subtotal_vnd'] as $field) {
                if ($this->db->fieldExists($field, 'bookings')) {
                    $this->forge->dropColumn('bookings', $field);
                }
            }
        }

        $this->forge->dropTable('promotion_codes', true);
    }

    private function createPromotionCodesTable(): void
    {
        if ($this->db->tableExists('promotion_codes')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'code' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => false],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => false],
            'description' => ['type' => 'TEXT', 'null' => true],
            'discount_type' => ['type' => 'ENUM', 'constraint' => ['fixed', 'percent'], 'null' => false, 'default' => 'fixed'],
            'discount_value' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => false, 'default' => '0.00'],
            'max_discount_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
            'min_order_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => false, 'default' => '0.00'],
            'usage_limit' => ['type' => 'INT', 'null' => false, 'default' => 0],
            'used_count' => ['type' => 'INT', 'null' => false, 'default' => 0],
            'starts_at' => ['type' => 'DATETIME', 'null' => true],
            'ends_at' => ['type' => 'DATETIME', 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('code', false, true);
        $this->forge->createTable('promotion_codes', true);
    }

    private function createPromotionCodeToursTable(): void
    {
        if ($this->db->tableExists('promotion_code_tours')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'promotion_code_id' => ['type' => 'INT', 'null' => false],
            'tour_id' => ['type' => 'INT', 'null' => false],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['promotion_code_id', 'tour_id']);
        $this->forge->addKey('tour_id');
        $this->forge->createTable('promotion_code_tours', true);
    }

    private function addBookingDiscountColumns(): void
    {
        if (! $this->db->tableExists('bookings')) {
            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('subtotal_vnd', 'bookings')) {
            $fields['subtotal_vnd'] = ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true, 'after' => 'infant_price'];
        }

        if (! $this->db->fieldExists('discount_amount_vnd', 'bookings')) {
            $fields['discount_amount_vnd'] = ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true, 'after' => 'subtotal_vnd'];
        }

        if (! $this->db->fieldExists('coupon_id', 'bookings')) {
            $fields['coupon_id'] = ['type' => 'INT', 'null' => true, 'after' => 'discount_amount_vnd'];
        }

        if (! $this->db->fieldExists('coupon_code', 'bookings')) {
            $fields['coupon_code'] = ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'coupon_id'];
        }

        if (! $this->db->fieldExists('coupon_snapshot', 'bookings')) {
            $fields['coupon_snapshot'] = ['type' => 'TEXT', 'null' => true, 'after' => 'coupon_code'];
        }

        if ($fields !== []) {
            $this->forge->addColumn('bookings', $fields);
        }
    }
}
