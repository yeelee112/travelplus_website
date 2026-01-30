<?php

namespace App\Models;

use CodeIgniter\Model;

class PostModel extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title_en','title_vi','slug','content_en','content_vi','published_at','published'];

    public function getRecent($limit = 3)
    {
        return $this->where('published', 1)->orderBy('published_at', 'desc')->findAll($limit);
    }
}
