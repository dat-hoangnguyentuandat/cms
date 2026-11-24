<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'is_active',
        'db_driver',
        'db_host',
        'db_port',
        'db_database',
        'db_username',
        'db_password',
        'db_options',
    ];

    protected $hidden = [
        'db_password',
        'db_options',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'db_password' => 'encrypted',
        'db_options' => 'encrypted:array',
    ];

    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomain::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(TenantPackage::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(PlatformAuditLog::class);
    }

    public function getConnectionName()
    {
        return (string) config('tenancy.central_connection', 'central');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
