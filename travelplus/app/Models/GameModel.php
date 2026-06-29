<?php

namespace App\Models;

use CodeIgniter\Model;

class GameModel extends Model
{
    protected $table = 'game_games';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'room_code',
        'title',
        'status',
        'current_number',
        'max_winners',
        'reset_count',
        'started_at',
        'ended_at',
    ];
}
