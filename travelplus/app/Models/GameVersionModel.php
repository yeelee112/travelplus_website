<?php

namespace App\Models;

use CodeIgniter\Model;

class GameVersionModel extends Model
{
    protected $table = 'game_versions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['game_id', 'version'];
}
