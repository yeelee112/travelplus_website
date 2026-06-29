<?php

namespace App\Repositories;

use App\Models\WinnerModel;
use CodeIgniter\Database\BaseConnection;

class WinnerRepository
{
    private BaseConnection $db;
    private WinnerModel $winners;

    public function __construct()
    {
        $this->db = db_connect();
        $this->winners = new WinnerModel();
    }

    public function listForGame(int $gameId): array
    {
        return $this->db->table('game_winners w')
            ->select('w.id, w.player_id, w.winner_position, w.created_at, p.name')
            ->join('game_players p', 'p.id = w.player_id')
            ->where('w.game_id', $gameId)
            ->orderBy('w.winner_position', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function countForGame(int $gameId): int
    {
        return (int) $this->winners->where('game_id', $gameId)->countAllResults();
    }
}
