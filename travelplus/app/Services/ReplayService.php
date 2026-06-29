<?php

namespace App\Services;

use App\Repositories\EventRepository;

class ReplayService
{
    public function timeline(int $gameId, ?string $filter = null): array
    {
        return (new EventRepository())->listForGame($gameId, $filter);
    }
}
