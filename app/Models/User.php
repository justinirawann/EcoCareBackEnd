<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'users';

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'address'
    ];

    protected $hidden = [
        'password',
    ];
}
