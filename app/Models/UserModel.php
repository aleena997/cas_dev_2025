<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['google_id', 'email', 'name', 'picture','phone', 'access_token', 'refresh_token'];
}
