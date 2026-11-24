<?php

namespace App\Tenancy;

use App\Models\TenantPackage;
use Illuminate\Support\Collection;

class TenantPackageAccess
{
    private array $names = [];

    public function __construct(private TenantContext $context) {}

    public function allows(string $type, string $name): bool
    {
        if (! config('tenancy.enabled')) {
            return true;
        }

        return $this->names($type)->contains($name);
    }

    public function names(string $type): Collection
    {
        if (! config('tenancy.enabled')) {
            return collect();
        }

        $tenantId = $this->context->id();
        if ($tenantId === null) {
            return collect();
        }

        $key = $tenantId.':'.$type;

        return $this->names[$key] ??= TenantPackage::query()
            ->where('tenant_id', $tenantId)
            ->where('package_type', $type)
            ->pluck('package_name');
    }

    public function forget(): void
    {
        $this->names = [];
    }
}
