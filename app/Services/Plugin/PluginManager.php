<?php

namespace App\Services\Plugin;

use App\Exceptions\PluginException;
use App\Models\Plugin;
use App\Services\Admin\PermissionRegistry;
use App\Services\Admin\RoutePermissionRegistry;
use App\Services\Widget\WidgetRegistry;
use App\Tenancy\TenantPackageAccess;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ZipArchive;

class PluginManager
{
    private array $registeredProviders = [];

    public function __construct(
        private string $pluginsPath,
        private ?TenantPackageAccess $packages = null,
    ) {}

    public function list(): Collection
    {
        File::ensureDirectoryExists($this->pluginsPath);
        $records = Schema::hasTable('plugins') ? Plugin::all()->keyBy('name') : collect();

        return collect(File::directories($this->pluginsPath))
            ->map(function (string $dir) use ($records) {
                $manifest = $this->readManifest($dir);
                if ($manifest === null) {
                    return null;
                }

                $name = $manifest['name'];
                if (! $this->isAvailable($name)) {
                    return null;
                }
                $record = $records->get($name);
                if (! $record && Schema::hasTable('plugins')) {
                    $record = $this->syncRecord($manifest, $dir, false);
                }

                return [
                    'name' => $name,
                    'title' => $manifest['title'] ?? $name,
                    'version' => $manifest['version'] ?? '1.0.0',
                    'author' => $manifest['author'] ?? null,
                    'description' => $manifest['description'] ?? null,
                    'is_active' => (bool) ($record?->is_active ?? false),
                    'installed_at' => $record?->installed_at,
                    'updated_at' => $record?->updated_at,
                    'path' => $dir,
                    'manifest' => $manifest,
                    'has_settings' => ! empty($manifest['settings_route'])
                        || ! empty($manifest['settings'])
                        || ! empty($manifest['settings_schema']),
                    'settings_route' => $manifest['settings_route'] ?? null,
                ];
            })
            ->filter()
            ->values();
    }

    public function catalog(): Collection
    {
        if (! File::isDirectory($this->pluginsPath)) {
            return collect();
        }

        return collect(File::directories($this->pluginsPath))
            ->map(function (string $dir) {
                $manifest = $this->readManifest($dir);
                if ($manifest === null) {
                    return null;
                }

                return [
                    'name' => $manifest['name'],
                    'title' => $manifest['title'] ?? $manifest['name'],
                    'version' => $manifest['version'] ?? '1.0.0',
                    'author' => $manifest['author'] ?? null,
                    'description' => $manifest['description'] ?? null,
                    'path' => $dir,
                    'manifest' => $manifest,
                ];
            })
            ->filter()
            ->values();
    }

    public function install(UploadedFile $file): array
    {
        $target = $this->extractPackage($file);
        $manifest = $this->readManifest($target);
        if ($manifest === null) {
            File::deleteDirectory($target);
            throw new PluginException('Plugin không hợp lệ: thiếu plugin.json.');
        }

        $name = $manifest['name'];
        $finalPath = $this->path($name);

        if (File::isDirectory($finalPath)) {
            File::deleteDirectory($finalPath);
        }

        File::ensureDirectoryExists(dirname($finalPath));
        File::moveDirectory($target, $finalPath, true);
        if (Schema::hasTable('plugins')) {
            $this->syncRecord($manifest, $finalPath, Plugin::where('name', $name)->value('is_active') ?? false);
        }
        $this->publishAssets($name);

        return $manifest;
    }

    public function activate(string $name): void
    {
        if (! $this->isAvailable($name)) {
            throw new PluginException("Plugin '{$name}' không được cấp cho tenant này.");
        }

        $dir = $this->path($name);
        $manifest = $this->readManifest($dir);
        if ($manifest === null) {
            throw new PluginException("Plugin '{$name}' không tồn tại hoặc thiếu plugin.json.");
        }

        $this->syncRecord($manifest, $dir, true);
        $this->runMigrations($name);
        $this->publishAssets($name);
        $this->clearCaches();
    }

    public function deactivate(string $name): void
    {
        if (! $this->isAvailable($name)) {
            throw new PluginException("Plugin '{$name}' không được cấp cho tenant này.");
        }

        Plugin::where('name', $name)->update(['is_active' => false]);
        $this->clearCaches();
    }

    public function delete(string $name): void
    {
        $record = Plugin::where('name', $name)->first();
        if ($record?->is_active) {
            throw new PluginException('Không thể gỡ plugin đang kích hoạt. Hãy tắt plugin trước.');
        }

        $dir = $this->path($name);
        if (File::isDirectory($dir)) {
            File::deleteDirectory($dir);
        }

        $assetDir = public_path('plugin-assets/'.$name);
        if (File::isDirectory($assetDir)) {
            File::deleteDirectory($assetDir);
        }

        Plugin::where('name', $name)->delete();
    }

    public function deleteFromCatalog(string $name): void
    {
        $dir = $this->path($name);
        if ($this->readManifest($dir) === null) {
            throw new PluginException("Plugin '{$name}' không tồn tại trong catalog.");
        }

        File::deleteDirectory($dir);

        $assetDir = public_path('plugin-assets/'.$name);
        if (File::isDirectory($assetDir)) {
            File::deleteDirectory($assetDir);
        }

        $this->clearCaches();
    }

    public function bootActive(): void
    {
        if (! File::isDirectory($this->pluginsPath) || ! Schema::hasTable('plugins')) {
            return;
        }

        foreach (Plugin::where('is_active', true)->get() as $plugin) {
            if (! $this->isAvailable($plugin->name)) {
                continue;
            }

            $manifest = $this->readManifest($this->path($plugin->name)) ?: $plugin->manifest;
            if (! is_array($manifest)) {
                continue;
            }

            $this->registerManifestExtensions($manifest);
            $this->bootPluginProvider($plugin->name, $manifest);
        }
    }

    public function activeMiddleware(): array
    {
        if (! File::isDirectory($this->pluginsPath) || ! Schema::hasTable('plugins')) {
            return [];
        }

        return Plugin::query()
            ->where('is_active', true)
            ->get()
            ->filter(fn (Plugin $plugin) => $this->isAvailable($plugin->name))
            ->flatMap(function (Plugin $plugin) {
                $manifest = $this->readManifest($this->path($plugin->name)) ?: $plugin->manifest;

                return is_array($manifest['middleware'] ?? null)
                    ? $manifest['middleware']
                    : [];
            })
            ->filter(fn ($middleware) => is_string($middleware) && class_exists($middleware))
            ->values()
            ->all();
    }

    /**
     * Register code and routes from the shared plugin catalog without reading a
     * tenant database. Per-tenant activation is enforced by route middleware
     * and bootActive().
     */
    public function discoverInstalled(): void
    {
        if (! File::isDirectory($this->pluginsPath)) {
            return;
        }

        foreach (File::directories($this->pluginsPath) as $dir) {
            $manifest = $this->readManifest($dir);
            if ($manifest === null) {
                continue;
            }

            $this->loadPlugin($manifest['name'], $manifest);
        }
    }

    public function path(string $name): string
    {
        return $this->pluginsPath.DIRECTORY_SEPARATOR.$name;
    }

    public function isAvailable(string $name): bool
    {
        return $this->packageAccess()->allows('plugin', $name);
    }

    public function readManifest(string $dir): ?array
    {
        $file = $dir.DIRECTORY_SEPARATOR.'plugin.json';
        if (! File::exists($file)) {
            return null;
        }

        $manifest = json_decode(File::get($file), true);
        if (! is_array($manifest)) {
            return null;
        }

        $name = $manifest['name'] ?? basename($dir);
        $name = Str::slug($name);
        if ($name === '') {
            return null;
        }

        $manifest['name'] = $name;
        $manifest['title'] = $manifest['title'] ?? $name;
        $manifest['version'] = $manifest['version'] ?? '1.0.0';

        return $manifest;
    }

    private function syncRecord(array $manifest, string $dir, bool $active): Plugin
    {
        return Plugin::updateOrCreate(
            ['name' => $manifest['name']],
            [
                'title' => $manifest['title'] ?? $manifest['name'],
                'version' => $manifest['version'] ?? '1.0.0',
                'author' => $manifest['author'] ?? null,
                'description' => $manifest['description'] ?? null,
                'is_active' => $active,
                'path' => $dir,
                'manifest' => $manifest,
                'installed_at' => now(),
            ],
        );
    }

    private function extractPackage(UploadedFile $file): string
    {
        $zip = new ZipArchive;
        if ($zip->open($file->getRealPath()) !== true) {
            throw new PluginException('Không thể mở file plugin.');
        }

        $root = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = str_replace('\\', '/', $zip->getNameIndex($i));
            if ($this->unsafeZipPath($name)) {
                $zip->close();
                throw new PluginException('File plugin chứa đường dẫn không an toàn.');
            }

            if (basename($name) === 'plugin.json') {
                $root = trim(dirname($name), '.\/');
            }
        }

        if ($root === null) {
            $zip->close();
            throw new PluginException('Plugin không hợp lệ: thiếu plugin.json.');
        }

        $temp = storage_path('app/plugin-upload-'.Str::random(12));
        File::ensureDirectoryExists($temp);
        $zip->extractTo($temp);
        $zip->close();

        return $root === '' ? $temp : $temp.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $root);
    }

    private function unsafeZipPath(string $name): bool
    {
        return str_starts_with($name, '/')
            || preg_match('/^[a-zA-Z]:\//', $name)
            || str_contains($name, '../')
            || str_contains($name, '..\\');
    }

    private function loadPlugin(string $name, array $manifest): void
    {
        $dir = $this->path($name);
        if (! File::isDirectory($dir)) {
            return;
        }

        if (File::isDirectory($dir.'/resources/views')) {
            app('view')->addNamespace('plugin-'.$name, $dir.'/resources/views');
        }

        foreach (['web', 'admin', 'api'] as $type) {
            $routeFile = $dir.'/routes/'.$type.'.php';
            if (! File::exists($routeFile)) {
                continue;
            }

            if ($type === 'api') {
                Route::middleware(['api', 'plugin.active:'.$name])->prefix('api/plugins/'.$name)->name('api.plugins.'.$name.'.')->group($routeFile);
            } elseif ($type === 'admin') {
                Route::middleware(['web', 'plugin.active:'.$name, 'admin.auth'])->prefix('admin/plugins/'.$name)->name('admin.plugins.'.$name.'.')->group($routeFile);
            } else {
                Route::middleware(['web', 'plugin.active:'.$name])->group($routeFile);
            }
        }

        $this->registerPluginProvider($name, $manifest);
    }

    private function registerPluginProvider(string $name, array $manifest): void
    {
        $dir = $this->path($name);
        $provider = $manifest['provider'] ?? null;
        $providerFile = $dir.'/src/Plugin.php';
        if ($provider && File::exists($providerFile)) {
            require_once $providerFile;
            if (class_exists($provider)) {
                $instance = app()->make($provider);
                if (method_exists($instance, 'register')) {
                    $instance->register(app());
                }
                $this->registeredProviders[$name] = $instance;
            }
        }
    }

    private function bootPluginProvider(string $name, array $manifest): void
    {
        if (! isset($this->registeredProviders[$name])) {
            $this->registerPluginProvider($name, $manifest);
        }

        $instance = $this->registeredProviders[$name] ?? null;
        if ($instance && method_exists($instance, 'boot')) {
            $instance->boot(app());
        }
    }

    private function registerManifestExtensions(array $manifest): void
    {
        if (! empty($manifest['permissions']) && is_array($manifest['permissions'])) {
            app(PermissionRegistry::class)->registerMany($manifest['permissions']);
        }

        if (! empty($manifest['route_permissions']) && is_array($manifest['route_permissions'])) {
            app(RoutePermissionRegistry::class)->registerMany($manifest['route_permissions']);
        }

        if (! empty($manifest['widgets']) && is_array($manifest['widgets'])) {
            $widgets = [];
            foreach ($manifest['widgets'] as $type => $definition) {
                if (is_array($definition)) {
                    $widgets[$type] = $definition + ['plugin' => $manifest['name']];
                }
            }
            app(WidgetRegistry::class)->registerWidgets($widgets);
        }
    }

    private function runMigrations(string $name): void
    {
        $path = $this->path($name).'/database/migrations';
        if (! File::isDirectory($path)) {
            return;
        }

        Artisan::call('migrate', [
            '--path' => str_replace(base_path().DIRECTORY_SEPARATOR, '', $path),
            '--force' => true,
        ]);
    }

    private function publishAssets(string $name): void
    {
        $source = $this->path($name).'/assets';
        if (! File::isDirectory($source)) {
            return;
        }

        $target = public_path('plugin-assets/'.$name);
        if (File::isDirectory($target)) {
            File::deleteDirectory($target);
        }
        File::copyDirectory($source, $target);
    }

    private function clearCaches(): void
    {
        try {
            Artisan::call('route:clear');
            Artisan::call('view:clear');
        } catch (\Throwable) {
            //
        }
    }

    private function packageAccess(): TenantPackageAccess
    {
        return $this->packages ?? app(TenantPackageAccess::class);
    }
}
