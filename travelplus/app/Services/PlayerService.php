<?php

namespace App\Services;

use App\Models\BoardCellModel;
use App\Models\PlayerMarkModel;
use App\Models\PlayerModel;
use App\Repositories\DrawRepository;
use App\Repositories\GameRepository;
use App\Repositories\PlayerRepository;
use RuntimeException;

class PlayerService
{
    private PlayerModel $players;
    private PlayerMarkModel $marks;
    private BoardCellModel $cells;
    private GameService $games;
    private BoardService $boards;
    private EventService $events;

    public function __construct()
    {
        $this->players = new PlayerModel();
        $this->marks = new PlayerMarkModel();
        $this->cells = new BoardCellModel();
        $this->games = new GameService();
        $this->boards = new BoardService();
        $this->events = new EventService();
    }

    public function join(string $roomCode, string $name): array
    {
        $game = $this->games->requireGame($roomCode);
        if (! in_array($game['status'], ['created', 'open'], true)) {
            throw new RuntimeException('Game đã bắt đầu. Người chơi mới không thể tham gia.');
        }

        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 120) {
            throw new RuntimeException('Tên người chơi không hợp lệ.');
        }

        $playerId = (int) $this->players->insert([
            'game_id' => (int) $game['id'],
            'name' => $name,
            'join_token' => bin2hex(random_bytes(24)),
            'status' => 'active',
            'last_seen_at' => date('Y-m-d H:i:s'),
        ], true);
        $this->boards->ensureBoard((int) $game['id'], $playerId);
        $this->events->log((int) $game['id'], 'PLAYER_JOINED', ['name' => $name], $playerId);
        $this->games->bumpVersion((int) $game['id']);

        return $this->players->find($playerId);
    }

    public function mark(int $playerId, int $number): array
    {
        $player = (new PlayerRepository())->find($playerId);
        if ($player === null) {
            throw new RuntimeException('Không tìm thấy người chơi.');
        }

        $game = $this->games->getGameById((int) $player['game_id']);
        if ($game['status'] !== 'running') {
            throw new RuntimeException('Game chưa bắt đầu.');
        }

        $number = max(0, min(255, $number));
        $drawnNumbers = (new DrawRepository())->numbersForGame((int) $game['id']);
        if (! in_array($number, $drawnNumbers, true)) {
            throw new RuntimeException('Số này chưa được xổ.');
        }

        $boardData = $this->boards->boardForPlayer($playerId);
        $cell = $this->cells
            ->where('board_id', (int) $boardData['board']['id'])
            ->where('number', $number)
            ->first();
        if (! is_array($cell)) {
            throw new RuntimeException('Số này không có trên bảng của bạn.');
        }

        $existing = $this->marks->where('player_id', $playerId)->where('number', $number)->first();
        if (is_array($existing)) {
            throw new RuntimeException('Số này đã được đánh dấu.');
        }

        $wasReady = $this->boards->completedLineCountForPlayer($playerId) >= 2;
        $this->marks->insert([
            'game_id' => (int) $game['id'],
            'player_id' => $playerId,
            'board_cell_id' => (int) $cell['id'],
            'number' => $number,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->cells->update((int) $cell['id'], ['marked' => 1]);

        $completedLines = $this->boards->completedLineCountForPlayer($playerId);
        $isReady = $completedLines >= 2;
        $readyNow = false;
        if (! $wasReady && $isReady && empty($player['ready_bingo_at'])) {
            $this->players->update($playerId, ['ready_bingo_at' => date('Y-m-d H:i:s')]);
            $this->events->log((int) $game['id'], 'READY_BINGO', ['number' => $number, 'completed_lines' => $completedLines], $playerId);
            $readyNow = true;
        }

        $this->events->log((int) $game['id'], 'NUMBER_MARKED', ['number' => $number], $playerId);
        $this->games->bumpVersion((int) $game['id']);

        return [
            'number' => $number,
            'ready_bingo' => $isReady,
            'ready_now' => $readyNow,
            'completed_lines' => $completedLines,
        ];
    }

    public function heartbeat(int $playerId, int $gameId): bool
    {
        $updated = (new PlayerRepository())->updateHeartbeat($playerId, $gameId);
        if ($updated) {
            (new GameRepository())->bumpVersion($gameId);
        }

        return $updated;
    }

    public function leave(int $playerId): array
    {
        $player = (new PlayerRepository())->find($playerId);
        if ($player === null) {
            throw new RuntimeException('Không tìm thấy người chơi.');
        }

        $this->events->log((int) $player['game_id'], 'PLAYER_LEFT', ['name' => $player['name']], $playerId);
        $this->games->bumpVersion((int) $player['game_id']);

        return ['player_id' => $playerId];
    }
}
