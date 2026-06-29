<?php

namespace App\Controllers;

class DisplayController extends BaseController
{
    public function index(): string
    {
        return view('bingo/room_entry', [
            'mode' => 'display',
            'title' => 'Màn hình Bingo',
            'targetPath' => 'display',
            'allowCreate' => false,
        ]);
    }

    public function show(string $roomCode = ''): string
    {
        return view('bingo/display', ['roomCode' => strtoupper($roomCode)]);
    }
}
