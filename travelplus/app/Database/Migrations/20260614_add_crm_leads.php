<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCrmLeads extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('crm_leads')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'source' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'stage' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => false, 'default' => 'new'],
            'priority' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false, 'default' => 'normal'],
            'customer_name' => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'customer_email' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'customer_phone' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'service_type' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'interest_title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'interest_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'destination' => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'travel_date' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'travelers' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'budget' => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'message' => ['type' => 'TEXT', 'null' => true],
            'booking_id' => ['type' => 'INT', 'null' => true],
            'booking_code' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'last_contacted_at' => ['type' => 'DATETIME', 'null' => true],
            'assigned_user_id' => ['type' => 'INT', 'null' => true],
            'internal_note' => ['type' => 'TEXT', 'null' => true],
            'metadata' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('source');
        $this->forge->addKey('stage');
        $this->forge->addKey('priority');
        $this->forge->addKey('customer_email');
        $this->forge->addKey('customer_phone');
        $this->forge->addKey('booking_id');
        $this->forge->createTable('crm_leads', true);
    }

    public function down()
    {
        $this->forge->dropTable('crm_leads', true);
    }
}
