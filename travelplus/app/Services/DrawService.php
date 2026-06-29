<?php

namespace App\Services;

use App\Models\DrawNumberModel;
use App\Repositories\DrawRepository;
use App\Repositories\WinnerRepository;
use RuntimeException;

class DrawService
{
    private DrawNumberModel $draws;
    private GameService $games;
    private EventService $events;

    public function __construct()
    {
        $this->draws = new DrawNumberModel();
        $this->games = new GameService();
        $this->events = new EventService();
    }

    public function draw(string $roomCode): array
    {
        $game = $this->games->requireGame($roomCode);
        $gameId = (int) $game['id'];
        if ($game['status'] !== 'running') {
            throw new RuntimeException('Game chưa bắt đầu.');
        }

        if ((new WinnerRepository())->countForGame($gameId) >= (int) $game['max_winners']) {
            throw new RuntimeException('Game đã đủ số người thắng.');
        }

        $drawn = (new DrawRepository())->numbersForGame($gameId);
        if (count($drawn) >= 90) {
            $this->games->endGame($roomCode);
            throw new RuntimeException('Tất cả số đã được xổ.');
        }

        $available = array_values(array_diff(range(1, 90), $drawn));
        $number = $available[random_int(0, count($available) - 1)];
        $order = count($drawn) + 1;

        $this->draws->insert([
            'game_id' => $gameId,
            'number' => $number,
            'draw_order' => $order,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        (new \App\Models\GameModel())->update($gameId, ['current_number' => $number]);
        $this->events->log($gameId, 'NUMBER_DRAWN', ['number' => $number, 'draw_order' => $order]);
        $this->games->bumpVersion($gameId);

        return ['number' => $number, 'draw_order' => $order];
    }
}
