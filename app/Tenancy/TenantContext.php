<?php

namespace App\Tenancy;

use App\Models\Tenant;
use LogicException;

class TenantContext
{
    private ?Tenant $tenant = null;

    public function set(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function get(): Tenant
    {
        return $this->tenant ?? throw new LogicException('No tenant has been initialized.');
    }

    public function id(): ?int
    {
        return $this->tenant?->getKey();
    }

    public function check(): bool
    {
        return $this->tenant !== null;
    }

    public function clear(): void
    {
        $this->tenant = null;
    }
}
