<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;

    protected $allowedFields = [
        'full_name',
        'email',
        'username',
        'password_hash',
        'phone',
        'is_admin',
        'status',
        'auth_session_version',
        'last_login_at',
        'created_at',
        'updated_at',
    ];
}
