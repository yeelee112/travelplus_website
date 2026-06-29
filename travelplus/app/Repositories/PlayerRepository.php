<?php

namespace App\Repositories;

use App\Models\PlayerModel;
use CodeIgniter\Database\BaseConnection;

class PlayerRepository
{
    private BaseConnection $db;
    private PlayerModel $players;

    public function __construct()
    {
        $this->db = db_connect();
        $this->players = new PlayerModel();
    }

    public function find(int $playerId): ?array
    {
        $player = $this->players->find($playerId);

        return is_array($player) ? $player : null;
    }

    public function listForGame(int $gameId): array
    {
        return $this->players
            ->where('game_id', $gameId)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    public function readyPlayers(int $gameId): array
    {
        return $this->players
            ->select('id, name, ready_bingo_at')
            ->where('game_id', $gameId)
            ->where('ready_bingo_at IS NOT NULL', null, false)
            ->orderBy('ready_bingo_at', 'ASC')
            ->findAll();
    }

    public function updateHeartbeat(int $playerId, int $gameId, int $minSeconds = 12): bool
    {
        $player = $this->players
            ->select('id, last_seen_at')
            ->where('id', $playerId)
            ->where('game_id', $gameId)
            ->first();

        if (! is_array($player)) {
            return false;
        }

        $lastSeen = strtotime((string) ($player['last_seen_at'] ?? '')) ?: 0;
        if ($lastSeen > 0 && time() - $lastSeen < $minSeconds) {
            return false;
        }

        return (bool) $this->players->update($playerId, ['last_seen_at' => date('Y-m-d H:i:s')]);
    }
}
