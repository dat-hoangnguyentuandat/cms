<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantDomain extends Model
{
    public $timestamps = false;

    protected $fillable = ['tenant_id', 'domain', 'is_primary'];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getConnectionName()
    {
        return (string) config('tenancy.central_connection', 'central');
    }
}
