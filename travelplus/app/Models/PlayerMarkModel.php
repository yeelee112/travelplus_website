<?php

namespace App\Models;

use CodeIgniter\Model;

class PlayerMarkModel extends Model
{
    protected $table = 'game_player_marks';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['game_id', 'player_id', 'board_cell_id', 'number', 'created_at'];
}
