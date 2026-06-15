<?php

namespace App\Models;

use CodeIgniter\Model;

class PromotionCodeTourModel extends Model
{
    protected $table = 'promotion_code_tours';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';

    protected $allowedFields = [
        'promotion_code_id',
        'tour_id',
        'created_at',
        'updated_at',
    ];
}
