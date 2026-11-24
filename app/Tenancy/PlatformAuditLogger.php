<?php

namespace App\Tenancy;

use App\Models\PlatformAuditLog;
use App\Models\Tenant;

class PlatformAuditLogger
{
    public function log(string $action, ?Tenant $tenant = null, array $metadata = []): void
    {
        $request = app()->bound('request') ? request() : null;
        $admin = $request?->attributes->get('platformAdmin');

        PlatformAuditLog::query()->create([
            'platform_admin_id' => $admin?->id,
            'tenant_id' => $tenant?->id,
            'action' => $action,
            'metadata' => $metadata ?: null,
            'ip_address' => $request?->ip(),
            'created_at' => now(),
        ]);
    }
}
