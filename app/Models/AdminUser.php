<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
    protected $fillable = ['username', 'password', 'full_name', 'email', 'role', 'role_id', 'avatar', 'is_active', 'last_login'];

    protected $hidden = ['password'];

    protected $casts = ['is_active' => 'boolean', 'last_login' => 'datetime'];

    public function getAuthIdentifierName()
    {
        return 'username';
    }

    public function adminRole()
    {
        return $this->belongsTo(AdminRole::class, 'role_id');
    }

    public function hasAdminPermission(string $key): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        return $this->adminRole?->hasPermission($key) ?? false;
    }
}
