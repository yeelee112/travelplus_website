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
        'address',
        'address_line',
        'province_code',
        'ward_code',
        'is_admin',
        'status',
        'auth_session_version',
        'email_verified_at',
        'phone_verified_at',
        'verification_channel',
        'last_login_at',
        'created_at',
        'updated_at',
    ];
}
