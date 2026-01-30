<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name_en','name_vi','slug','image'];

    public function getTop($limit = 8)
    {
        return $this->orderBy('id', 'asc')->findAll($limit);
    }
}
