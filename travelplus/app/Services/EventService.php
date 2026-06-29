<?php

namespace App\Services;

use App\Repositories\EventRepository;

class EventService
{
    private EventRepository $events;

    public function __construct()
    {
        $this->events = new EventRepository();
    }

    public function log(int $gameId, string $type, array $data = [], ?int $playerId = null): void
    {
        $this->events->create($gameId, $type, $data, $playerId);
    }
}
