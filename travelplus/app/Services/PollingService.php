<?php

namespace App\Services;

use App\Repositories\GameRepository;
use RuntimeException;

class PollingService
{
    private GameRepository $games;

    public function __construct()
    {
        $this->games = new GameRepository();
    }

    public function updates(string $roomCode, int $clientVersion = 0, ?int $playerId = null, bool $heartbeat = false): array
    {
        $versionRow = $this->games->findVersionByRoomCode($roomCode);
        if ($versionRow === null) {
            throw new RuntimeException('Không tìm thấy phòng.');
        }

        if ($heartbeat && $playerId !== null && $playerId > 0) {
            (new PlayerService())->heartbeat($playerId, (int) $versionRow['id']);
            $versionRow = $this->games->findVersionByRoomCode($roomCode);
        }

        $serverVersion = (int) $versionRow['version'];
        if ($clientVersion > 0 && $clientVersion === $serverVersion) {
            return [
                'changed' => false,
                'version' => $serverVersion,
            ];
        }

        return [
            'changed' => true,
            'version' => $serverVersion,
            'state' => (new GameService())->statusPayload((int) $versionRow['id']),
        ];
    }
}
