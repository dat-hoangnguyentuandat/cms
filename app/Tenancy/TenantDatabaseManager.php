<?php

namespace App\Tenancy;

use App\Models\Setting;
use App\Models\Tenant;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class TenantDatabaseManager
{
    private ?array $original = null;

    private bool $preservePlatformSession = false;

    public function __construct(
        private Application $app,
        private TenantContext $context,
        private TenantPackageAccess $packages,
    ) {}

    public function initialize(Tenant $tenant): void
    {
        $connection = (string) config('tenancy.tenant_connection', 'tenant');
        $this->rememberOriginalConfiguration();
        $this->preservePlatformSession = $this->app->bound('request')
            && (bool) $this->app->make('request')->attributes->get('platform_runtime');

        config([
            "database.connections.{$connection}" => $this->connectionConfiguration($tenant),
            'database.default' => $connection,
            'session.connection' => $connection,
            'cache.stores.database.connection' => $connection,
            'cache.stores.database.lock_connection' => $connection,
            'queue.connections.database.connection' => $connection,
            'queue.failed.database' => $connection,
            'queue.batching.database' => $connection,
            'cache.prefix' => 'cms:tenant:'.$tenant->uuid.':',
        ]);

        DB::purge($connection);
        DB::setDefaultConnection($connection);

        if (! $this->preservePlatformSession) {
            $this->forgetSessionDriver();
        }
        $this->scopeFilesystem($tenant);
        Setting::clearCache();
        $this->packages->forget();
        $this->context->set($tenant);
    }

    public function end(): void
    {
        if ($this->original === null) {
            return;
        }

        $connection = (string) config('tenancy.tenant_connection', 'tenant');
        DB::purge($connection);
        DB::setDefaultConnection($this->original['database.default']);
        config($this->original);

        foreach (['local', 'public'] as $disk) {
            Storage::forgetDisk($disk);
        }
        if (! $this->preservePlatformSession) {
            $this->forgetSessionDriver();
        }
        $this->forgetCacheDriver();
        Setting::clearCache();
        $this->packages->forget();
        $this->context->clear();
        $this->original = null;
        $this->preservePlatformSession = false;
    }

    private function connectionConfiguration(Tenant $tenant): array
    {
        $driver = strtolower((string) $tenant->db_driver);
        if (! in_array($driver, ['mysql', 'mariadb', 'pgsql', 'sqlsrv', 'sqlite'], true)) {
            throw new InvalidArgumentException("Unsupported tenant database driver '{$driver}'.");
        }

        $base = config("database.connections.{$driver}", []);
        if ($driver === 'sqlite') {
            $database = (string) $tenant->db_database;
            if ($database !== ':memory:' && ! $this->isAbsolutePath($database)) {
                $database = database_path($database);
            }

            return array_replace($base, [
                'url' => null,
                'database' => $database,
            ]);
        }

        return array_replace($base, array_filter([
            'url' => null,
            'host' => $tenant->db_host,
            'port' => $tenant->db_port,
            'database' => $tenant->db_database,
            'username' => $tenant->db_username,
            'password' => $tenant->db_password,
        ], static fn ($value) => $value !== null));
    }

    private function scopeFilesystem(Tenant $tenant): void
    {
        $root = storage_path('app/tenants/'.$tenant->uuid);
        $publicRoot = $root.DIRECTORY_SEPARATOR.'public';
        $privateRoot = $root.DIRECTORY_SEPARATOR.'private';
        $sessionRoot = storage_path('framework/tenants/'.$tenant->uuid.'/sessions');
        $cacheRoot = storage_path('framework/tenants/'.$tenant->uuid.'/cache');
        File::ensureDirectoryExists($publicRoot);
        File::ensureDirectoryExists($privateRoot);
        File::ensureDirectoryExists($sessionRoot);
        File::ensureDirectoryExists($cacheRoot);

        config([
            'filesystems.disks.public.root' => $publicRoot,
            'filesystems.disks.local.root' => $privateRoot,
            'session.files' => $sessionRoot,
            'cache.stores.file.path' => $cacheRoot,
            'cache.stores.file.lock_path' => $cacheRoot,
        ]);

        foreach (['local', 'public'] as $disk) {
            Storage::forgetDisk($disk);
        }
        $this->forgetCacheDriver();
    }

    private function rememberOriginalConfiguration(): void
    {
        if ($this->original !== null) {
            return;
        }

        $keys = [
            'database.default',
            'session.connection',
            'session.files',
            'cache.stores.database.connection',
            'cache.stores.database.lock_connection',
            'cache.stores.file.path',
            'cache.stores.file.lock_path',
            'cache.prefix',
            'queue.connections.database.connection',
            'queue.failed.database',
            'queue.batching.database',
            'filesystems.disks.public.root',
            'filesystems.disks.local.root',
        ];

        foreach ($keys as $key) {
            $this->original[$key] = config($key);
        }
    }

    private function forgetCacheDriver(): void
    {
        if ($this->app->bound('cache')) {
            Cache::forgetDriver((string) config('cache.default'));
        }
    }

    private function forgetSessionDriver(): void
    {
        if ($this->app->bound('session')) {
            $this->app->make('session')->forgetDrivers();
        }
        $this->app->forgetInstance('session.store');
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, DIRECTORY_SEPARATOR)
            || (bool) preg_match('/^[a-zA-Z]:[\\\\\/]/', $path);
    }
}
