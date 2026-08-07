<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLoyaltyRewardVouchers extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('loyalty_reward_vouchers')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'promotion_code_id' => ['type' => 'INT'],
            'code' => ['type' => 'VARCHAR', 'constraint' => 50],
            'reward_key' => ['type' => 'VARCHAR', 'constraint' => 50],
            'points_spent' => ['type' => 'INT', 'unsigned' => true],
            'voucher_amount_vnd' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'min_order_vnd' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'issued'],
            'expires_at' => ['type' => 'DATETIME'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->addUniqueKey('promotion_code_id');
        $this->forge->addKey(['user_id', 'created_at']);
        $this->forge->createTable('loyalty_reward_vouchers', true);
    }

    public function down()
    {
        $this->forge->dropTable('loyalty_reward_vouchers', true);
    }
}
