<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameBingoTablesWithGamePrefix extends Migration
{
    private array $renames = [
        'games' => 'game_games',
        'players' => 'game_players',
        'boards' => 'game_boards',
        'board_cells' => 'game_board_cells',
        'draw_numbers' => 'game_draw_numbers',
        'player_marks' => 'game_player_marks',
        'winners' => 'game_winners',
    ];

    public function up()
    {
        foreach ($this->renames as $from => $to) {
            if ($this->db->tableExists($from) && ! $this->db->tableExists($to)) {
                $this->forge->renameTable($from, $to);
            }
        }
    }

    public function down()
    {
        foreach (array_reverse($this->renames) as $from => $to) {
            if ($this->db->tableExists($to) && ! $this->db->tableExists($from)) {
                $this->forge->renameTable($to, $from);
            }
        }
    }
}
