<?php

namespace App\Models;

use CodeIgniter\Model;

class PlayerModel extends Model
{
    protected $table = 'game_players';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'game_id',
        'name',
        'join_token',
        'status',
        'ready_bingo_at',
        'bingo_at',
        'last_seen_at',
    ];
}
