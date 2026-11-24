<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class TenantListCommand extends Command
{
    protected $signature = 'tenant:list';

    protected $description = 'List configured tenants and domains';

    public function handle(): int
    {
        $rows = Tenant::query()
            ->with(['domains', 'packages'])
            ->orderBy('name')
            ->get()
            ->map(fn (Tenant $tenant) => [
                $tenant->uuid,
                $tenant->name,
                $tenant->is_active ? 'active' : 'disabled',
                $tenant->domains->pluck('domain')->implode(', '),
                $tenant->db_driver,
                $tenant->db_database,
                $tenant->packages
                    ->map(fn ($package) => $package->package_type.':'.$package->package_name)
                    ->implode(', '),
            ]);

        $this->table(['UUID', 'Name', 'Status', 'Domains', 'Driver', 'Database', 'Packages'], $rows);

        return self::SUCCESS;
    }
}
