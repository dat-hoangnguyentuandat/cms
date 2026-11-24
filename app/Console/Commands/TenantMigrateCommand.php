<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Tenancy\DomainNormalizer;
use App\Tenancy\TenantDatabaseManager;
use App\Tenancy\TenantMigrator;
use Illuminate\Console\Command;
use Throwable;

class TenantMigrateCommand extends Command
{
    protected $signature = 'tenant:migrate
        {tenant? : Tenant UUID or domain; omit to migrate all active tenants}
        {--activate : Activate a specifically selected tenant after successful migration}
        {--continue-on-error : Continue migrating remaining tenants after a failure}';

    protected $description = 'Run core and active-plugin migrations on tenant databases';

    public function handle(TenantDatabaseManager $databases, TenantMigrator $migrator): int
    {
        $tenants = $this->resolveTenants();
        if ($tenants->isEmpty()) {
            $this->error('No matching active tenant found.');

            return self::FAILURE;
        }

        $failed = false;
        foreach ($tenants as $tenant) {
            $this->newLine();
            $this->info("Migrating {$tenant->name} ({$tenant->uuid})");

            try {
                $databases->initialize($tenant);
                foreach ($migrator->migrate() as $run) {
                    $this->output->write($run['output']);
                    if ($run['exit_code'] !== 0) {
                        throw new \RuntimeException("Migration failed for {$run['path']}.");
                    }
                }
                if ($this->option('activate') && $this->argument('tenant')) {
                    $tenant->update(['is_active' => true]);
                    $this->info('Tenant activated.');
                }
            } catch (Throwable $e) {
                $failed = true;
                $this->error($e->getMessage());
                if (! $this->option('continue-on-error')) {
                    return self::FAILURE;
                }
            } finally {
                $databases->end();
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function resolveTenants()
    {
        $value = $this->argument('tenant');
        if (! $value) {
            return Tenant::query()->where('is_active', true)->orderBy('id')->get();
        }

        $tenant = Tenant::query()->where('uuid', $value)->first();
        if (! $tenant) {
            try {
                $domain = DomainNormalizer::normalize((string) $value);
                $tenant = TenantDomain::query()->with('tenant')->where('domain', $domain)->first()?->tenant;
            } catch (\InvalidArgumentException) {
                //
            }
        }

        return collect($tenant ? [$tenant] : []);
    }
}
