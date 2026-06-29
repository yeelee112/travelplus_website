<?php

namespace App\Controllers;

class HostController extends BaseController
{
    public function index(): string
    {
        return view('bingo/room_entry', [
            'mode' => 'host',
            'title' => 'Quản trị Bingo',
            'targetPath' => 'host',
            'allowCreate' => true,
        ]);
    }

    public function show(string $roomCode = ''): string
    {
        return view('bingo/host', ['roomCode' => strtoupper($roomCode)]);
    }
}
