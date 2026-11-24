<?php

namespace App\Tenancy;

use App\Models\Tenant;
use App\Models\TenantDomain;
use InvalidArgumentException;

class TenantLocator
{
    public function find(string $value): ?Tenant
    {
        $tenant = Tenant::query()->where('uuid', $value)->first();
        if ($tenant) {
            return $tenant;
        }

        try {
            $domain = DomainNormalizer::normalize($value);
        } catch (InvalidArgumentException) {
            return null;
        }

        return TenantDomain::query()
            ->with('tenant')
            ->where('domain', $domain)
            ->first()
            ?->tenant;
    }
}
