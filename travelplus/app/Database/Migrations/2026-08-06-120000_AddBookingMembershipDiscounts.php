<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBookingMembershipDiscounts extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('bookings')) {
            return;
        }

        $fields = $this->db->getFieldNames('bookings');
        $add = [];

        if (! in_array('membership_tier_key', $fields, true)) {
            $add['membership_tier_key'] = [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'subtotal_vnd',
            ];
        }
        if (! in_array('membership_discount_rate', $fields, true)) {
            $add['membership_discount_rate'] = [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => '0.00',
                'after' => 'membership_tier_key',
            ];
        }
        if (! in_array('membership_discount_amount_vnd', $fields, true)) {
            $add['membership_discount_amount_vnd'] = [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => '0.00',
                'after' => 'membership_discount_rate',
            ];
        }

        if ($add !== []) {
            $this->forge->addColumn('bookings', $add);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('bookings')) {
            return;
        }

        $fields = $this->db->getFieldNames('bookings');
        foreach (['membership_discount_amount_vnd', 'membership_discount_rate', 'membership_tier_key'] as $field) {
            if (in_array($field, $fields, true)) {
                $this->forge->dropColumn('bookings', $field);
            }
        }
    }
}
