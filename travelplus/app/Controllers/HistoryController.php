<?php

namespace App\Controllers;

class HistoryController extends BaseController
{
    public function index(): string
    {
        $db = db_connect();
        $games = $db->table('game_games g')
            ->select('g.id, g.room_code, g.status, g.created_at, g.started_at, g.ended_at, COUNT(DISTINCT p.id) AS player_count, GROUP_CONCAT(DISTINCT wp.name ORDER BY w.winner_position SEPARATOR ", ") AS winners')
            ->join('game_players p', 'p.game_id = g.id', 'left')
            ->join('game_winners w', 'w.game_id = g.id', 'left')
            ->join('game_players wp', 'wp.id = w.player_id', 'left')
            ->groupBy('g.id, g.room_code, g.status, g.created_at, g.started_at, g.ended_at')
            ->orderBy('g.created_at', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();

        return view('bingo/history', ['games' => $games]);
    }
}
