<?php

namespace App\Models;

use CodeIgniter\Model;

class BoardCellModel extends Model
{
    protected $table = 'game_board_cells';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['board_id', 'row', 'column', 'number', 'marked'];
}
