<?php

namespace App\Models;

use CodeIgniter\Model;

class GameEventModel extends Model
{
    protected $table = 'game_events';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['game_id', 'player_id', 'event_type', 'event_data', 'created_at'];
}
