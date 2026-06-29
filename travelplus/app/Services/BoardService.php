<?php

namespace App\Services;

use App\Models\BoardCellModel;
use App\Models\BoardModel;
use App\Models\PlayerModel;
use App\Models\PlayerMarkModel;
use App\Repositories\BoardRepository;
use App\Repositories\PlayerRepository;
use RuntimeException;

class BoardService
{
    private BoardModel $boards;
    private BoardCellModel $cells;
    private PlayerMarkModel $marks;
    private BoardRepository $boardRepository;

    public function __construct()
    {
        $this->boards = new BoardModel();
        $this->cells = new BoardCellModel();
        $this->marks = new PlayerMarkModel();
        $this->boardRepository = new BoardRepository();
    }

    public function ensureBoard(int $gameId, int $playerId): array
    {
        $board = $this->boardRepository->findByPlayer($playerId);
        if ($board !== null) {
            return $board;
        }

        $boardId = (int) $this->boards->insert(['game_id' => $gameId, 'player_id' => $playerId], true);
        $this->insertRandomCells($boardId);

        return $this->boards->find($boardId);
    }

    public function regenerateForPlayer(int $playerId): array
    {
        $player = (new PlayerRepository())->find($playerId);
        if ($player === null) {
            throw new RuntimeException('Không tìm thấy người chơi.');
        }

        $gameService = new GameService();
        $game = $gameService->getGameById((int) $player['game_id']);
        if (! in_array($game['status'], ['created', 'open'], true)) {
            throw new RuntimeException('Chỉ có thể đổi bảng số trước khi game bắt đầu.');
        }

        $board = $this->ensureBoard((int) $game['id'], $playerId);
        $boardId = (int) $board['id'];
        $db = db_connect();

        $db->transStart();
        $this->marks->where('player_id', $playerId)->delete();
        $this->cells->where('board_id', $boardId)->delete();
        $this->insertRandomCells($boardId);
        (new PlayerModel())->update($playerId, [
            'ready_bingo_at' => null,
            'bingo_at' => null,
            'status' => 'active',
        ]);
        (new EventService())->log((int) $game['id'], 'BOARD_REGENERATED', [], $playerId);
        $gameService->bumpVersion((int) $game['id']);
        $db->transComplete();

        if (! $db->transStatus()) {
            throw new RuntimeException('Không thể đổi bảng số.');
        }

        return $this->boardForPlayer($playerId);
    }

    private function insertRandomCells(int $boardId): void
    {
        $numbers = range(1, 90);
        shuffle($numbers);
        $numbers = array_slice($numbers, 0, 25);

        $rows = [];
        for ($i = 0; $i < 25; $i++) {
            $rows[] = [
                'board_id' => $boardId,
                'row' => intdiv($i, 5) + 1,
                'column' => ($i % 5) + 1,
                'number' => $numbers[$i],
                'marked' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        $this->cells->insertBatch($rows);
    }

    public function boardForPlayer(int $playerId): array
    {
        $board = $this->boardRepository->findByPlayer($playerId);
        if ($board === null) {
            throw new RuntimeException('Không tìm thấy bảng số.');
        }

        return [
            'board' => $board,
            'cells' => $this->boardRepository->cells((int) $board['id']),
        ];
    }

    public function hasRequiredLines(int $playerId, array $additionalNumber = []): bool
    {
        return $this->completedLineCountForPlayer($playerId, $additionalNumber) >= 2;
    }

    public function completedLineCountForPlayer(int $playerId, array $additionalNumber = []): int
    {
        $boardData = $this->boardForPlayer($playerId);
        $markedNumbers = $this->markedNumbers($playerId);
        foreach ($additionalNumber as $number) {
            $markedNumbers[] = (int) $number;
        }
        $markedNumbers = array_values(array_unique($markedNumbers));

        return $this->completedLineCountFromCells($boardData['cells'], $markedNumbers);
    }

    public function markedNumbers(int $playerId): array
    {
        return array_map('intval', array_column(
            $this->marks->select('number')->where('player_id', $playerId)->findAll(),
            'number'
        ));
    }

    public function hasRequiredLinesFromCells(array $cells, array $markedNumbers): bool
    {
        return $this->completedLineCountFromCells($cells, $markedNumbers) >= 2;
    }

    public function completedLineCountFromCells(array $cells, array $markedNumbers): int
    {
        $marked = array_flip(array_map('intval', $markedNumbers));
        $grid = [];
        foreach ($cells as $cell) {
            $grid[(int) $cell['row']][(int) $cell['column']] = isset($marked[(int) $cell['number']]);
        }

        $completed = 0;
        for ($i = 1; $i <= 5; $i++) {
            $rowComplete = true;
            $columnComplete = true;
            for ($j = 1; $j <= 5; $j++) {
                $rowComplete = $rowComplete && ! empty($grid[$i][$j]);
                $columnComplete = $columnComplete && ! empty($grid[$j][$i]);
            }
            if ($rowComplete) {
                $completed++;
            }
            if ($columnComplete) {
                $completed++;
            }
        }

        $diagA = true;
        $diagB = true;
        for ($i = 1; $i <= 5; $i++) {
            $diagA = $diagA && ! empty($grid[$i][$i]);
            $diagB = $diagB && ! empty($grid[$i][6 - $i]);
        }

        if ($diagA) {
            $completed++;
        }
        if ($diagB) {
            $completed++;
        }

        return $completed;
    }
}
