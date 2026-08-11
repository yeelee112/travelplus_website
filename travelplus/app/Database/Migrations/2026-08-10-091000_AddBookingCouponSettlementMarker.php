<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBookingCouponSettlementMarker extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('bookings')) {
            return;
        }

        $fields = $this->db->getFieldNames('bookings');
        if (! in_array('coupon_settled_at', $fields, true)) {
            $this->forge->addColumn('bookings', [
                'coupon_settled_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'coupon_snapshot',
                ],
            ]);
        }

        // Existing paid bookings have already consumed their coupon. Mark them
        // as settled so a later refund/cancellation can restore usage exactly once.
        $this->db->query(<<<'SQL'
            UPDATE bookings
            SET coupon_settled_at = COALESCE(paid_at, updated_at, created_at, NOW())
            WHERE payment_status = 'paid'
              AND coupon_id IS NOT NULL
              AND coupon_settled_at IS NULL
        SQL);
    }

    public function down()
    {
        if ($this->db->tableExists('bookings')
            && in_array('coupon_settled_at', $this->db->getFieldNames('bookings'), true)) {
            $this->forge->dropColumn('bookings', 'coupon_settled_at');
        }
    }
}
