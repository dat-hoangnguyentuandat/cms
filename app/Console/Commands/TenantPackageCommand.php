<?php

namespace App\Console\Commands;

use App\Models\TenantPackage;
use App\Tenancy\TenantLocator;
use App\Tenancy\TenantPackageManager;
use Illuminate\Console\Command;
use Throwable;

class TenantPackageCommand extends Command
{
    protected $signature = 'tenant:package
        {tenant : Tenant UUID or domain}
        {type : theme or plugin}
        {name : Package name}
        {--revoke : Remove package access instead of assigning it}
        {--replace : Legacy alias for assigning and activating a theme}
        {--activate : Activate the assigned theme or plugin}';

    protected $description = 'Assign or revoke a production theme/plugin for one tenant';

    public function handle(TenantLocator $locator, TenantPackageManager $packages): int
    {
        $tenant = $locator->find((string) $this->argument('tenant'));
        if (! $tenant) {
            $this->error('Tenant not found.');

            return self::FAILURE;
        }

        $type = strtolower((string) $this->argument('type'));
        $name = (string) $this->argument('name');
        if (! in_array($type, [TenantPackage::TYPE_THEME, TenantPackage::TYPE_PLUGIN], true)) {
            $this->error("Package type must be 'theme' or 'plugin'.");

            return self::FAILURE;
        }

        try {
            if ($type === TenantPackage::TYPE_THEME) {
                if ($this->option('revoke')) {
                    $packages->revokeTheme($tenant, $name);
                } else {
                    $packages->assignTheme(
                        $tenant,
                        $name,
                        (bool) ($this->option('activate') || $this->option('replace')),
                    );
                }
            } elseif ($this->option('revoke')) {
                $packages->revokePlugin($tenant, $name);
            } else {
                $packages->assignPlugin($tenant, $name, (bool) $this->option('activate'));
            }

            $verb = $this->option('revoke') ? 'Revoked' : 'Assigned';
            $this->info("{$verb} {$type}:{$name} for {$tenant->name}.");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
