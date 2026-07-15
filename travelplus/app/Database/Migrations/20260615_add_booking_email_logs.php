<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBookingEmailLogs extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('booking_email_logs')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'booking_id' => ['type' => 'INT', 'null' => true],
            'booking_code' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'email_type' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'recipient_email' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => false],
            'subject' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => false, 'default' => 'sent'],
            'error_message' => ['type' => 'TEXT', 'null' => true],
            'sent_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('booking_id');
        $this->forge->addKey('booking_code');
        $this->forge->addKey('email_type');
        $this->forge->addKey('recipient_email');
        $this->forge->addKey('status');
        $this->forge->addKey('sent_at');
        $this->forge->createTable('booking_email_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('booking_email_logs', true);
    }
}
