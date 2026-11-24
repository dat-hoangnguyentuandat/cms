<?php

namespace App\Console\Commands;

use App\Tenancy\TenantProvisioner;
use Illuminate\Console\Command;
use Throwable;

class TenantCreateCommand extends Command
{
    protected $signature = 'tenant:create
        {name : Customer/site name}
        {domain : Primary domain}
        {--alias=* : Additional domain}
        {--driver=mysql : mysql, mariadb, pgsql, sqlsrv or sqlite}
        {--host=127.0.0.1}
        {--port=}
        {--database=}
        {--username=}
        {--password=}
        {--create-database : Create the MySQL/MariaDB database using the central connection}
        {--theme=default : Activate a theme from the shared catalog}
        {--plugin=* : Activate a plugin from the shared catalog}
        {--admin-user= : Create the initial administrator username}
        {--admin-email= : Initial administrator email}
        {--admin-password= : Initial administrator password (minimum 12 characters)}
        {--no-migrate : Register only; do not migrate or initialize the tenant database}';

    protected $description = 'Provision a tenant with its domains and isolated database';

    public function handle(TenantProvisioner $provisioner): int
    {
        try {
            $database = (string) ($this->option('database')
                ?: $provisioner->defaultDatabaseName((string) $this->argument('name')));

            $tenant = $provisioner->provision([
                'name' => (string) $this->argument('name'),
                'primary_domain' => (string) $this->argument('domain'),
                'aliases' => $this->option('alias'),
                'db_driver' => (string) $this->option('driver'),
                'db_host' => (string) $this->option('host'),
                'db_port' => $this->option('port') ?: null,
                'db_database' => $database,
                'db_username' => $this->option('username') ?: null,
                'db_password' => $this->option('password') ?: null,
                'create_database' => (bool) $this->option('create-database'),
                'theme' => (string) $this->option('theme'),
                'plugins' => $this->option('plugin'),
                'admin_user' => $this->option('admin-user'),
                'admin_email' => $this->option('admin-email'),
                'admin_password' => $this->option('admin-password'),
                'no_migrate' => (bool) $this->option('no-migrate'),
            ]);

            $primary = $tenant->domains->firstWhere('is_primary', true)?->domain;
            $this->info("Tenant '{$tenant->name}' created: {$primary}");
            $this->line("UUID: {$tenant->uuid}");
            $this->line("Database: {$tenant->db_driver}:{$tenant->db_database}");
            if (! $tenant->is_active) {
                $this->warn('Tenant remains disabled. Run tenant:migrate <uuid> --activate when its database is ready.');
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
