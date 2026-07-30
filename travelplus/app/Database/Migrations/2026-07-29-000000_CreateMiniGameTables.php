<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMiniGameTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'plate_code' => ['type' => 'VARCHAR', 'constraint' => 20],
            'province' => ['type' => 'VARCHAR', 'constraint' => 120],
            'places' => ['type' => 'TEXT', 'null' => true],
            'specialty' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'airport' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'unesco' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'sort_order' => ['type' => 'INT', 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ])->addKey('id', true)->createTable('game_questions', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'office' => ['type' => 'VARCHAR', 'constraint' => 60],
            'token' => ['type' => 'CHAR', 'constraint' => 64],
            'score' => ['type' => 'INT', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ])->addKey('id', true)->addUniqueKey('token')->createTable('game_players', true);

        $this->forge->addField([
            'id' => ['type' => 'TINYINT', 'unsigned' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'waiting'],
            'question_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'question_number' => ['type' => 'INT', 'default' => 0],
            'answer_revealed' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'buzz_open' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'countdown_seconds' => ['type' => 'INT', 'default' => 20],
            'countdown_ends_at' => ['type' => 'DATETIME', 'null' => true],
            'main_points' => ['type' => 'INT', 'default' => 2],
            'bonus_points' => ['type' => 'INT', 'default' => 1],
            'bonus_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'bonus_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'version' => ['type' => 'BIGINT', 'unsigned' => true, 'default' => 1],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ])->addKey('id', true)->createTable('game_state', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'question_id' => ['type' => 'INT', 'unsigned' => true],
            'round_no' => ['type' => 'INT', 'default' => 1],
            'player_id' => ['type' => 'INT', 'unsigned' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'waiting'],
            'buzzed_at' => ['type' => 'DATETIME', 'null' => false],
            'buzz_order' => ['type' => 'INT'],
        ])->addKey('id', true)
          ->addUniqueKey(['question_id', 'round_no', 'player_id'])
          ->addKey(['question_id', 'round_no', 'buzz_order'])
          ->createTable('game_buzzes', true);

        $this->db->table('game_state')->insert(['id' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    public function down()
    {
        foreach (['game_buzzes', 'game_state', 'game_players', 'game_questions'] as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}
