<?php

namespace App\Controllers;

class PlayerController extends BaseController
{
    public function index(): string
    {
        return view('bingo/room_entry', [
            'mode' => 'play',
            'title' => 'Tham gia Bingo',
            'targetPath' => 'play',
            'allowCreate' => false,
        ]);
    }

    public function show(string $roomCode = ''): string
    {
        return view('bingo/player', ['roomCode' => strtoupper($roomCode)]);
    }
}
