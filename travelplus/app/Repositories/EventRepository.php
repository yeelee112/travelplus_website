<?php

namespace App\Repositories;

use App\Models\GameEventModel;
use CodeIgniter\Database\BaseConnection;

class EventRepository
{
    private BaseConnection $db;
    private GameEventModel $events;

    public function __construct()
    {
        $this->db = db_connect();
        $this->events = new GameEventModel();
    }

    public function listForGame(int $gameId, ?string $filter = null): array
    {
        $builder = $this->db->table('game_events e')
            ->select('e.id, e.game_id, e.player_id, e.event_type, e.event_data, e.created_at, p.name AS player_name')
            ->join('game_players p', 'p.id = e.player_id', 'left')
            ->where('e.game_id', $gameId)
            ->orderBy('e.created_at', 'ASC')
            ->orderBy('e.id', 'ASC');

        if ($filter !== null && $filter !== '') {
            $builder->whereIn('e.event_type', $this->eventTypesForFilter($filter));
        }

        return $builder->get()->getResultArray();
    }

    public function create(int $gameId, string $type, array $data = [], ?int $playerId = null): int
    {
        $this->events->insert([
            'game_id' => $gameId,
            'player_id' => $playerId,
            'event_type' => $type,
            'event_data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->events->getInsertID();
    }

    private function eventTypesForFilter(string $filter): array
    {
        return match ($filter) {
            'draw' => ['NUMBER_DRAWN'],
            'player' => ['PLAYER_JOINED', 'PLAYER_LEFT', 'NUMBER_MARKED', 'PLAYER_BINGO'],
            'ready' => ['READY_BINGO'],
            'winner' => ['WINNER_CONFIRMED', 'GAME_FINISHED'],
            default => [$filter],
        };
    }
}
