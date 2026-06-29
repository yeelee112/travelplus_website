<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBingoTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'room_code' => ['type' => 'VARCHAR', 'constraint' => 40],
            'title' => ['type' => 'VARCHAR', 'constraint' => 120, 'default' => 'TravelPlus Bingo'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'created'],
            'current_number' => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'max_winners' => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 3],
            'reset_count' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'started_at' => ['type' => 'DATETIME', 'null' => true],
            'ended_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('room_code', 'uq_bingo_games_room_code');
        $this->forge->addKey('status', false, false, 'idx_bingo_games_status');
        $this->forge->createTable('game_games', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'game_id' => ['type' => 'INT', 'unsigned' => true],
            'version' => ['type' => 'BIGINT', 'unsigned' => true, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('game_id', 'uq_bingo_game_versions_game_id');
        $this->forge->createTable('game_versions', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'game_id' => ['type' => 'INT', 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'join_token' => ['type' => 'VARCHAR', 'constraint' => 80],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'ready_bingo_at' => ['type' => 'DATETIME', 'null' => true],
            'bingo_at' => ['type' => 'DATETIME', 'null' => true],
            'last_seen_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['game_id', 'status'], false, false, 'idx_bingo_players_game_status');
        $this->forge->addKey(['game_id', 'ready_bingo_at'], false, false, 'idx_bingo_players_ready');
        $this->forge->addKey(['game_id', 'last_seen_at'], false, false, 'idx_bingo_players_seen');
        $this->forge->addUniqueKey('join_token', 'uq_bingo_players_join_token');
        $this->forge->createTable('game_players', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'game_id' => ['type' => 'INT', 'unsigned' => true],
            'player_id' => ['type' => 'INT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('player_id', 'uq_bingo_boards_player_id');
        $this->forge->addKey('game_id', false, false, 'idx_bingo_boards_game_id');
        $this->forge->createTable('game_boards', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'board_id' => ['type' => 'INT', 'unsigned' => true],
            'row' => ['type' => 'TINYINT', 'unsigned' => true],
            'column' => ['type' => 'TINYINT', 'unsigned' => true],
            'number' => ['type' => 'TINYINT', 'unsigned' => true],
            'marked' => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['board_id', 'row', 'column'], 'uq_bingo_board_cells_position');
        $this->forge->addUniqueKey(['board_id', 'number'], 'uq_bingo_board_cells_number');
        $this->forge->addKey(['board_id', 'marked'], false, false, 'idx_bingo_board_cells_marked');
        $this->forge->createTable('game_board_cells', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'game_id' => ['type' => 'INT', 'unsigned' => true],
            'number' => ['type' => 'TINYINT', 'unsigned' => true],
            'draw_order' => ['type' => 'TINYINT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['game_id', 'number'], 'uq_bingo_draw_numbers_number');
        $this->forge->addUniqueKey(['game_id', 'draw_order'], 'uq_bingo_draw_numbers_order');
        $this->forge->createTable('game_draw_numbers', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'game_id' => ['type' => 'INT', 'unsigned' => true],
            'player_id' => ['type' => 'INT', 'unsigned' => true],
            'board_cell_id' => ['type' => 'INT', 'unsigned' => true],
            'number' => ['type' => 'TINYINT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['player_id', 'number'], 'uq_bingo_player_marks_number');
        $this->forge->addKey(['game_id', 'player_id'], false, false, 'idx_bingo_player_marks_game_player');
        $this->forge->createTable('game_player_marks', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'game_id' => ['type' => 'INT', 'unsigned' => true],
            'player_id' => ['type' => 'INT', 'unsigned' => true],
            'winner_position' => ['type' => 'TINYINT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['game_id', 'player_id'], 'uq_bingo_winners_player');
        $this->forge->addUniqueKey(['game_id', 'winner_position'], 'uq_bingo_winners_position');
        $this->forge->createTable('game_winners', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'game_id' => ['type' => 'INT', 'unsigned' => true],
            'player_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'event_type' => ['type' => 'VARCHAR', 'constraint' => 40],
            'event_data' => ['type' => 'JSON', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['game_id', 'created_at'], false, false, 'idx_bingo_events_timeline');
        $this->forge->addKey(['game_id', 'event_type'], false, false, 'idx_bingo_events_type');
        $this->forge->createTable('game_events', true);
    }

    public function down()
    {
        foreach (['game_events', 'game_winners', 'game_player_marks', 'game_draw_numbers', 'game_board_cells', 'game_boards', 'game_players', 'game_versions', 'game_games'] as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}
