<?php

namespace App\Services;

use App\Models\GameModel;
use App\Models\GameVersionModel;
use App\Repositories\GameRepository;
use App\Repositories\PlayerRepository;
use App\Repositories\WinnerRepository;
use CodeIgniter\Database\BaseConnection;
use RuntimeException;

class GameService
{
    private BaseConnection $db;
    private GameModel $games;
    private GameVersionModel $versions;
    private GameRepository $gameRepository;
    private EventService $events;

    public function __construct()
    {
        $this->db = db_connect();
        $this->games = new GameModel();
        $this->versions = new GameVersionModel();
        $this->gameRepository = new GameRepository();
        $this->events = new EventService();
    }

    public function createRoom(?string $roomCode = null): array
    {
        $roomCode = strtoupper(trim((string) $roomCode));
        if ($roomCode === '') {
            $roomCode = 'TP-BINGO-' . date('His');
        }

        if ($this->gameRepository->findByRoomCode($roomCode) !== null) {
            throw new RuntimeException('Mã phòng đã tồn tại.');
        }

        $this->db->transStart();
        $gameId = (int) $this->games->insert([
            'room_code' => $roomCode,
            'status' => 'created',
            'current_number' => null,
        ], true);
        $this->versions->insert(['game_id' => $gameId, 'version' => 1]);
        $this->events->log($gameId, 'GAME_CREATED', ['room_code' => $roomCode]);
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Không thể tạo phòng.');
        }

        return $this->getGameById($gameId);
    }

    public function openRoom(string $roomCode): array
    {
        $game = $this->requireGame($roomCode);
        $this->games->update((int) $game['id'], ['status' => 'open', 'ended_at' => null]);
        $this->events->log((int) $game['id'], 'ROOM_OPENED');
        $this->bumpVersion((int) $game['id']);

        return $this->getGameById((int) $game['id']);
    }

    public function startGame(string $roomCode): array
    {
        $game = $this->requireGame($roomCode);
        if (! in_array($game['status'], ['created', 'open'], true)) {
            throw new RuntimeException('Chỉ phòng vừa tạo hoặc đang mở mới có thể bắt đầu.');
        }

        $this->games->update((int) $game['id'], [
            'status' => 'running',
            'started_at' => date('Y-m-d H:i:s'),
            'ended_at' => null,
        ]);
        $this->events->log((int) $game['id'], 'GAME_STARTED');
        $this->bumpVersion((int) $game['id']);

        return $this->getGameById((int) $game['id']);
    }

    public function resetGame(string $roomCode): array
    {
        $game = $this->requireGame($roomCode);
        $gameId = (int) $game['id'];

        $this->db->transStart();
        $this->db->table('game_player_marks')->where('game_id', $gameId)->delete();
        $this->db->table('game_draw_numbers')->where('game_id', $gameId)->delete();
        $this->db->table('game_winners')->where('game_id', $gameId)->delete();
        $boardIds = array_map('intval', array_column(
            $this->db->table('game_boards')->select('id')->where('game_id', $gameId)->get()->getResultArray(),
            'id'
        ));
        if ($boardIds !== []) {
            $this->db->table('game_board_cells')
                ->whereIn('board_id', $boardIds)
                ->update(['marked' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
        }
        $this->db->table('game_players')->where('game_id', $gameId)->update([
            'ready_bingo_at' => null,
            'bingo_at' => null,
            'status' => 'active',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->games->update($gameId, [
            'status' => 'open',
            'current_number' => null,
            'ended_at' => null,
            'reset_count' => ((int) $game['reset_count']) + 1,
        ]);
        $this->events->log($gameId, 'GAME_RESET');
        $this->bumpVersion($gameId);
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Không thể chơi lại.');
        }

        return $this->getGameById($gameId);
    }

    public function endGame(string $roomCode): array
    {
        $game = $this->requireGame($roomCode);
        $gameId = (int) $game['id'];
        $this->games->update($gameId, ['status' => 'ended', 'ended_at' => date('Y-m-d H:i:s')]);
        $this->events->log($gameId, 'GAME_FINISHED', ['reason' => 'host_end']);
        $this->bumpVersion($gameId);

        return $this->getGameById($gameId);
    }

    public function statusPayload(int $gameId): array
    {
        $game = $this->getGameById($gameId);
        $draws = $this->db->table('game_draw_numbers')
            ->select('number, draw_order, created_at')
            ->where('game_id', $gameId)
            ->orderBy('draw_order', 'ASC')
            ->get()
            ->getResultArray();

        $players = (new PlayerRepository())->listForGame($gameId);
        $offlineCutoff = time() - 30;
        foreach ($players as &$player) {
            $lastSeen = strtotime((string) ($player['last_seen_at'] ?? '')) ?: 0;
            $player['online'] = $lastSeen >= $offlineCutoff;
        }
        unset($player);

        $winners = (new WinnerRepository())->listForGame($gameId);
        $version = $this->db->table('game_versions')->select('version')->where('game_id', $gameId)->get(1)->getRowArray();

        return [
            'version' => (int) ($version['version'] ?? 1),
            'game' => $game,
            'drawn_numbers' => array_map(static fn ($row) => (int) $row['number'], $draws),
            'draws' => $draws,
            'current_number' => $game['current_number'] !== null ? (int) $game['current_number'] : null,
            'players' => $players,
            'player_count' => count($players),
            'online_count' => count(array_filter($players, static fn ($player) => (bool) $player['online'])),
            'offline_count' => count(array_filter($players, static fn ($player) => ! (bool) $player['online'])),
            'ready_players' => (new PlayerRepository())->readyPlayers($gameId),
            'winners' => $winners,
            'winner_count' => count($winners),
            'server_time' => date('Y-m-d H:i:s'),
        ];
    }

    public function requireGame(string $roomCode): array
    {
        $game = $this->gameRepository->findByRoomCode($roomCode);
        if ($game === null) {
            throw new RuntimeException('Không tìm thấy phòng.');
        }

        return $game;
    }

    public function getGameById(int $gameId): array
    {
        $game = $this->games->find($gameId);
        if (! is_array($game)) {
            throw new RuntimeException('Không tìm thấy game.');
        }

        return $game;
    }

    public function bumpVersion(int $gameId): void
    {
        $this->gameRepository->bumpVersion($gameId);
    }
}
