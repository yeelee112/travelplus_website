<?php

namespace App\Services;

use App\Models\GameModel;
use App\Models\PlayerModel;
use App\Models\WinnerModel;
use App\Repositories\DrawRepository;
use App\Repositories\WinnerRepository;
use RuntimeException;

class WinnerService
{
    public function confirmBingo(int $playerId): array
    {
        $db = db_connect();
        $players = new PlayerModel();
        $winners = new WinnerModel();
        $games = new GameModel();
        $boardService = new BoardService();
        $eventService = new EventService();
        $gameService = new GameService();

        $player = $players->find($playerId);
        if (! is_array($player)) {
            throw new RuntimeException('Không tìm thấy người chơi.');
        }

        $gameId = (int) $player['game_id'];
        $db->transBegin();

        $lockedGame = $db->query('SELECT * FROM game_games WHERE id = ? FOR UPDATE', [$gameId])->getRowArray();
        if (! is_array($lockedGame)) {
            $db->transRollback();
            throw new RuntimeException('Không tìm thấy game.');
        }

        if ($lockedGame['status'] !== 'running') {
            $db->transRollback();
            throw new RuntimeException('Game chưa bắt đầu.');
        }

        if ($winners->where('game_id', $gameId)->where('player_id', $playerId)->first()) {
            $db->transRollback();
            throw new RuntimeException('Người chơi này đã thắng.');
        }

        $winnerCount = (new WinnerRepository())->countForGame($gameId);
        if ($winnerCount >= (int) $lockedGame['max_winners']) {
            $db->transRollback();
            throw new RuntimeException('Game đã đủ số người thắng.');
        }

        $markedNumbers = $boardService->markedNumbers($playerId);
        $drawnNumbers = (new DrawRepository())->numbersForGame($gameId);
        if (array_diff($markedNumbers, $drawnNumbers) !== []) {
            $db->transRollback();
            throw new RuntimeException('Phát hiện đánh dấu không hợp lệ.');
        }

        $boardData = $boardService->boardForPlayer($playerId);
        $completedLines = $boardService->completedLineCountFromCells($boardData['cells'], $markedNumbers);
        if ($completedLines < 2) {
            $db->transRollback();
            throw new RuntimeException('Bingo cần ít nhất 2 hàng hoàn chỉnh.');
        }

        $position = $winnerCount + 1;
        $now = date('Y-m-d H:i:s');
        $winners->insert([
            'game_id' => $gameId,
            'player_id' => $playerId,
            'winner_position' => $position,
            'created_at' => $now,
        ]);
        $players->update($playerId, ['bingo_at' => $now, 'status' => 'winner']);
        $eventService->log($gameId, 'PLAYER_BINGO', ['winner_position' => $position], $playerId);
        $eventService->log($gameId, 'WINNER_CONFIRMED', [
            'winner_position' => $position,
            'player_name' => $player['name'],
            'completed_lines' => $completedLines,
        ], $playerId);

        if ($position >= (int) $lockedGame['max_winners']) {
            $games->update($gameId, ['status' => 'finished', 'ended_at' => $now]);
            $eventService->log($gameId, 'GAME_FINISHED', ['reason' => 'max_winners']);
        }

        $gameService->bumpVersion($gameId);
        $db->transCommit();

        return [
            'winner_position' => $position,
            'player_id' => $playerId,
            'player_name' => $player['name'],
        ];
    }
}
