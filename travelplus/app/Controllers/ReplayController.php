<?php

namespace App\Controllers;

use App\Services\ReplayService;

class ReplayController extends BaseController
{
    public function show(int $gameId): string
    {
        $filter = $this->request->getGet('filter');

        return view('bingo/replay', [
            'gameId' => $gameId,
            'filter' => is_string($filter) ? $filter : '',
            'events' => (new ReplayService())->timeline($gameId, is_string($filter) ? $filter : null),
        ]);
    }
}
