<?php

namespace App\Repositories;

use App\Models\DrawNumberModel;

class DrawRepository
{
    private DrawNumberModel $draws;

    public function __construct()
    {
        $this->draws = new DrawNumberModel();
    }

    public function numbersForGame(int $gameId): array
    {
        return array_map('intval', array_column(
            $this->draws->select('number')->where('game_id', $gameId)->orderBy('draw_order', 'ASC')->findAll(),
            'number'
        ));
    }
}
