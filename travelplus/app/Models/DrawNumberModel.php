<?php

namespace App\Models;

use CodeIgniter\Model;

class DrawNumberModel extends Model
{
    protected $table = 'game_draw_numbers';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['game_id', 'number', 'draw_order', 'created_at'];
}
