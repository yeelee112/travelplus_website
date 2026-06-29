<?php

namespace App\Models;

use CodeIgniter\Model;

class WinnerModel extends Model
{
    protected $table = 'game_winners';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['game_id', 'player_id', 'winner_position', 'created_at'];
}
