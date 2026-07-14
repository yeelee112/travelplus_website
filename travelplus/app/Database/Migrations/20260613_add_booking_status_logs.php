<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBookingStatusLogs extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('booking_status_logs')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'auto_increment' => true],
                'booking_id' => ['type' => 'INT', 'null' => false],
                'from_status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
                'to_status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
                'amount_paid_vnd' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
                'provider_reference' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
                'actor_user_id' => ['type' => 'INT', 'null' => true],
                'actor_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'actor_email' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
                'note' => ['type' => 'TEXT', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('booking_id');
            $this->forge->addKey('to_status');
            $this->forge->addKey('created_at');
            $this->forge->createTable('booking_status_logs', true);

            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('amount_paid_vnd', 'booking_status_logs')) {
            $fields['amount_paid_vnd'] = ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true, 'after' => 'to_status'];
        }

        if (! $this->db->fieldExists('provider_reference', 'booking_status_logs')) {
            $fields['provider_reference'] = ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'amount_paid_vnd'];
        }

        if ($fields !== []) {
            $this->forge->addColumn('booking_status_logs', $fields);
        }
    }

    public function down()
    {
        $this->forge->dropTable('booking_status_logs', true);
    }
}
