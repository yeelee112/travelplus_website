<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMiniGameQuestionType extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('question_type', 'game_state')) {
            $this->forge->addColumn('game_state', [
                'question_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'default' => 'plate_to_province',
                    'after' => 'question_number',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('question_type', 'game_state')) {
            $this->forge->dropColumn('game_state', 'question_type');
        }
    }
}
