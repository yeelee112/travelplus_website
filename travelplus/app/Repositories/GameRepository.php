<?php

namespace App\Repositories;

use App\Models\GameModel;
use CodeIgniter\Database\BaseConnection;

class GameRepository
{
    private BaseConnection $db;
    private GameModel $games;

    public function __construct()
    {
        $this->db = db_connect();
        $this->games = new GameModel();
    }

    public function findByRoomCode(string $roomCode): ?array
    {
        $game = $this->games->where('room_code', strtoupper(trim($roomCode)))->first();

        return is_array($game) ? $game : null;
    }

    public function findVersionByRoomCode(string $roomCode): ?array
    {
        $row = $this->db->table('game_games g')
            ->select('g.id, g.room_code, g.status, gv.version')
            ->join('game_versions gv', 'gv.game_id = g.id')
            ->where('g.room_code', strtoupper(trim($roomCode)))
            ->get(1)
            ->getRowArray();

        return is_array($row) ? $row : null;
    }

    public function bumpVersion(int $gameId): void
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('game_versions')
            ->set('version', 'version + 1', false)
            ->set('updated_at', $now)
            ->where('game_id', $gameId)
            ->update();
    }
}
