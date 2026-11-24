<?php

namespace App\Tenancy;

use App\Models\AdminUser;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantPackage;
use App\Services\Plugin\PluginManager;
use App\Services\Theme\ThemeManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class TenantProvisioner
{
    public function __construct(
        private TenantDatabaseManager $databases,
        private TenantMigrator $migrator,
        private PluginManager $plugins,
        private ThemeManager $themes,
    ) {}

    public function provision(array $data): Tenant
    {
        $driver = strtolower(trim((string) ($data['db_driver'] ?? 'mysql')));
        $database = trim((string) ($data['db_database'] ?? ''));
        $this->validateDatabase($driver, $database);
        if ($driver === 'sqlite') {
            $database = $this->absoluteSqlitePath($database);
        }

        $primary = DomainNormalizer::normalize((string) ($data['primary_domain'] ?? ''));
        $domains = collect($data['aliases'] ?? [])
            ->map(fn ($domain) => DomainNormalizer::normalize((string) $domain))
            ->push($primary)
            ->unique()
            ->values();
        if (PlatformDomain::configured() !== null && $domains->contains(PlatformDomain::configured())) {
            throw new InvalidArgumentException('A tenant domain cannot be the configured Platform Admin domain.');
        }
        $this->assertDomainsAvailable($domains);

        $theme = trim((string) ($data['theme'] ?? ThemeManager::DEFAULT_THEME));
        $themeNames = collect($data['themes'] ?? [])
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->push($theme)
            ->unique()
            ->values();
        $pluginNames = collect($data['plugins'] ?? [])
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->values();
        $this->validatePackages($themeNames, $pluginNames);
        $this->validateAdministrator($data);

        if ($driver === 'sqlite') {
            $this->prepareSqliteDatabase($database);
        }
        if (! empty($data['create_database'])) {
            $this->createDatabase($driver, $database);
        }

        $central = (string) config('tenancy.central_connection', 'central');
        $tenant = DB::connection($central)->transaction(function () use (
            $data,
            $driver,
            $database,
            $domains,
            $primary,
            $themeNames,
            $pluginNames,
        ) {
            $tenant = Tenant::query()->create([
                'uuid' => (string) Str::uuid(),
                'name' => trim((string) $data['name']),
                'is_active' => false,
                'db_driver' => $driver,
                'db_host' => $driver === 'sqlite' ? null : ($data['db_host'] ?? null),
                'db_port' => $driver === 'sqlite' ? null : ($data['db_port'] ?? null),
                'db_database' => $database,
                'db_username' => $driver === 'sqlite' ? null : ($data['db_username'] ?? null),
                'db_password' => $driver === 'sqlite' ? null : ($data['db_password'] ?? null),
                'db_options' => $data['db_options'] ?? null,
            ]);

            foreach ($domains as $domain) {
                $tenant->domains()->create([
                    'domain' => $domain,
                    'is_primary' => $domain === $primary,
                ]);
            }

            foreach ($themeNames as $assignedTheme) {
                $tenant->packages()->create([
                    'package_type' => TenantPackage::TYPE_THEME,
                    'package_name' => $assignedTheme,
                ]);
            }
            foreach ($pluginNames as $plugin) {
                $tenant->packages()->create([
                    'package_type' => TenantPackage::TYPE_PLUGIN,
                    'package_name' => $plugin,
                ]);
            }

            return $tenant;
        });

        if (! empty($data['no_migrate'])) {
            return $tenant->load(['domains', 'packages']);
        }

        try {
            $this->databases->initialize($tenant);
            foreach ($this->migrator->migrate() as $run) {
                if ($run['exit_code'] !== 0) {
                    throw new RuntimeException("Migration failed for {$run['path']}: ".trim($run['output']));
                }
            }

            foreach ($pluginNames as $plugin) {
                $this->plugins->activate($plugin);
            }
            $this->themes->activate($theme);
            $this->createAdministrator($data);
        } catch (Throwable $e) {
            $tenant->update(['is_active' => false]);
            throw $e;
        } finally {
            $this->databases->end();
        }

        $tenant->update(['is_active' => (bool) ($data['is_active'] ?? true)]);

        return $tenant->load(['domains', 'packages']);
    }

    public function defaultDatabaseName(string $name): string
    {
        $slug = str_replace('-', '_', Str::slug($name));

        return 'cms_'.substr($slug ?: 'tenant', 0, 40).'_'.Str::lower(Str::random(6));
    }

    private function validateDatabase(string $driver, string $database): void
    {
        if (! in_array($driver, ['mysql', 'mariadb', 'pgsql', 'sqlsrv', 'sqlite'], true)) {
            throw new InvalidArgumentException("Unsupported driver '{$driver}'.");
        }
        if ($database === '') {
            throw new InvalidArgumentException('Database name/path is required.');
        }
    }

    private function assertDomainsAvailable(Collection $domains): void
    {
        $existing = TenantDomain::query()->whereIn('domain', $domains)->pluck('domain');
        if ($existing->isNotEmpty()) {
            throw new InvalidArgumentException('Domain already belongs to another tenant: '.$existing->implode(', '));
        }
    }

    private function validatePackages(Collection $themes, Collection $plugins): void
    {
        foreach ($themes as $theme) {
            if (! $this->themes->exists($theme)) {
                throw new InvalidArgumentException("Theme '{$theme}' is not installed in the production catalog.");
            }
        }

        foreach ($plugins as $plugin) {
            if ($this->plugins->readManifest($this->plugins->path($plugin)) === null) {
                throw new InvalidArgumentException("Plugin '{$plugin}' is not installed in the production catalog.");
            }
        }
    }

    private function validateAdministrator(array $data): void
    {
        $username = trim((string) ($data['admin_user'] ?? ''));
        $password = (string) ($data['admin_password'] ?? '');
        if ($username === '' && $password === '') {
            return;
        }
        if ($username === '' || strlen($password) < 12) {
            throw new InvalidArgumentException('Initial admin requires a username and password with at least 12 characters.');
        }
    }

    private function prepareSqliteDatabase(string $database): void
    {
        if ($database === ':memory:' || File::exists($database)) {
            return;
        }

        File::ensureDirectoryExists(dirname($database));
        touch($database);
    }

    private function absoluteSqlitePath(string $database): string
    {
        if ($database === ':memory:'
            || str_starts_with($database, DIRECTORY_SEPARATOR)
            || preg_match('/^[a-zA-Z]:[\\\\\/]/', $database)) {
            return $database;
        }

        return database_path($database);
    }

    private function createDatabase(string $driver, string $database): void
    {
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new InvalidArgumentException('Automatic database creation supports MySQL/MariaDB only.');
        }
        if (! preg_match('/^[a-zA-Z0-9_]+$/', $database)) {
            throw new InvalidArgumentException('MySQL database name may contain only letters, numbers and underscores.');
        }

        DB::connection((string) config('tenancy.central_connection', 'central'))->statement(
            "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );
    }

    private function createAdministrator(array $data): void
    {
        $username = trim((string) ($data['admin_user'] ?? ''));
        if ($username === '') {
            return;
        }

        AdminUser::query()->updateOrCreate(
            ['username' => $username],
            [
                'password' => Hash::make((string) $data['admin_password']),
                'full_name' => trim((string) ($data['admin_name'] ?? $username)) ?: $username,
                'email' => trim((string) ($data['admin_email'] ?? '')) ?: "{$username}@localhost.invalid",
                'role' => 'admin',
                'is_active' => true,
            ],
        );
    }
}
