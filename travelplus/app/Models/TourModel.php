<?php

namespace App\Models;

use CodeIgniter\Model;

class TourModel extends Model
{
    protected $table = 'tours';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title_en','title_vi','slug','description_en','description_vi','price','image','category_id','published'];

    public function getFeatured($limit = 3)
    {
        return $this->where('published', 1)->orderBy('id', 'desc')->findAll($limit);
    }
}
