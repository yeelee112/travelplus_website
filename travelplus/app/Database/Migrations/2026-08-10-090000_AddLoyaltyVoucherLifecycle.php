<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLoyaltyVoucherLifecycle extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('loyalty_reward_vouchers')) {
            return;
        }

        $fields = $this->db->getFieldNames('loyalty_reward_vouchers');
        $add = [];

        if (! in_array('booking_id', $fields, true)) {
            $add['booking_id'] = [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'after' => 'status',
            ];
        }
        if (! in_array('reserved_at', $fields, true)) {
            $add['reserved_at'] = ['type' => 'DATETIME', 'null' => true, 'after' => 'booking_id'];
        }
        if (! in_array('reservation_expires_at', $fields, true)) {
            $add['reservation_expires_at'] = ['type' => 'DATETIME', 'null' => true, 'after' => 'reserved_at'];
        }
        if (! in_array('used_at', $fields, true)) {
            $add['used_at'] = ['type' => 'DATETIME', 'null' => true, 'after' => 'reservation_expires_at'];
        }
        if (! in_array('released_at', $fields, true)) {
            $add['released_at'] = ['type' => 'DATETIME', 'null' => true, 'after' => 'used_at'];
        }

        if ($add !== []) {
            $this->forge->addColumn('loyalty_reward_vouchers', $add);
        }

        $indexes = $this->db->getIndexData('loyalty_reward_vouchers');
        if (! isset($indexes['booking_status'])) {
            $this->db->query('CREATE INDEX booking_status ON loyalty_reward_vouchers (booking_id, status)');
        }
        if (! isset($indexes['status_reservation_expires_at'])) {
            $this->db->query('CREATE INDEX status_reservation_expires_at ON loyalty_reward_vouchers (status, reservation_expires_at)');
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('loyalty_reward_vouchers')) {
            return;
        }

        $indexes = $this->db->getIndexData('loyalty_reward_vouchers');
        if (isset($indexes['booking_status'])) {
            $this->db->query('DROP INDEX booking_status ON loyalty_reward_vouchers');
        }
        if (isset($indexes['status_reservation_expires_at'])) {
            $this->db->query('DROP INDEX status_reservation_expires_at ON loyalty_reward_vouchers');
        }

        $fields = $this->db->getFieldNames('loyalty_reward_vouchers');
        foreach (['released_at', 'used_at', 'reservation_expires_at', 'reserved_at', 'booking_id'] as $field) {
            if (in_array($field, $fields, true)) {
                $this->forge->dropColumn('loyalty_reward_vouchers', $field);
            }
        }
    }
}
