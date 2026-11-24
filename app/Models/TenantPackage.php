<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPackage extends Model
{
    public const TYPE_THEME = 'theme';

    public const TYPE_PLUGIN = 'plugin';

    public $timestamps = false;

    protected $fillable = ['tenant_id', 'package_type', 'package_name'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getConnectionName()
    {
        return (string) config('tenancy.central_connection', 'central');
    }
}
