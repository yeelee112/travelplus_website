<?php

namespace App\Repositories;

use App\Models\BoardCellModel;
use App\Models\BoardModel;

class BoardRepository
{
    private BoardModel $boards;
    private BoardCellModel $cells;

    public function __construct()
    {
        $this->boards = new BoardModel();
        $this->cells = new BoardCellModel();
    }

    public function findByPlayer(int $playerId): ?array
    {
        $board = $this->boards->where('player_id', $playerId)->first();

        return is_array($board) ? $board : null;
    }

    public function cells(int $boardId): array
    {
        return $this->cells
            ->where('board_id', $boardId)
            ->orderBy('row', 'ASC')
            ->orderBy('column', 'ASC')
            ->findAll();
    }
}
