<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class PlatformAdmin extends Authenticatable
{
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getConnectionName()
    {
        return (string) config('tenancy.central_connection', 'central');
    }
}
