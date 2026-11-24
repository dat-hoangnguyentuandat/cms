<?php

namespace App\Services\Theme;

use App\Exceptions\ThemeInstallException;
use App\Models\HomepageBlock;
use App\Models\Setting;
use App\Services\Widget\WidgetRegistry;
use App\Tenancy\TenantPackageAccess;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ThemeManager
{
    public const DEFAULT_THEME = 'default';

    public const SETTING_KEY = 'active_theme';

    public function __construct(
        private ThemeInstaller $installer,
        private string $themesPath,
        private ?string $themePackagesPath = null,
        private ?TenantPackageAccess $packages = null,
    ) {}

    public function install(UploadedFile $file): array
    {
        $meta = $this->installer->install($file);
        $this->clearViewCache();
        $this->clearThemeCaches();

        return $meta;
    }

    public function list(): Collection
    {
        $active = $this->active();

        return $this->catalog()
            ->filter(fn (array $meta) => $this->isAvailable($meta['name']))
            ->map(function (array $meta) use ($active) {
                $meta['is_active'] = $meta['name'] === $active;

                return $meta;
            })
            ->values();
    }

    public function catalog(): Collection
    {
        if (! File::isDirectory($this->themesPath)) {
            return collect();
        }

        return collect(File::directories($this->themesPath))
            ->map(function (string $dir) {
                $meta = $this->readMeta($dir);
                if ($meta === null) {
                    return null;
                }

                $meta['path'] = $dir;
                $meta['installed'] = File::lastModified($dir);

                return $meta;
            })
            ->filter()
            ->values();
    }

    public function exists(string $name): bool
    {
        return File::isDirectory($this->themesPath.DIRECTORY_SEPARATOR.$name)
            && File::exists($this->themesPath.DIRECTORY_SEPARATOR.$name.'/theme.json');
    }

    public function active(): string
    {
        $configured = Setting::get(self::SETTING_KEY, self::DEFAULT_THEME) ?: self::DEFAULT_THEME;
        if (! config('tenancy.enabled')) {
            return $configured;
        }

        if ($this->isAvailable($configured) && $this->exists($configured)) {
            return $configured;
        }

        return $this->firstAvailable()
            ?? throw new ThemeInstallException('Tenant chưa được cấp theme hợp lệ.');
    }

    public function activate(string $name): void
    {
        if (! $this->exists($name)) {
            throw new ThemeInstallException("Theme '{$name}' chưa được cài đặt.");
        }
        if (! $this->isAvailable($name)) {
            throw new ThemeInstallException("Theme '{$name}' không được cấp cho tenant này.");
        }

        // Ensure the theme owns a full, isolated copy of the standard widget set.
        // Non-destructive: existing theme-specific widgets are preserved.
        if ($name !== self::DEFAULT_THEME) {
            app(ThemeWidgetSynchronizer::class)->sync($name);
        }

        DB::transaction(function () use ($name) {
            // Theme data and layout must switch together or not at all.
            app(ThemeSeeder::class)->run($this->path($name));
            $this->resetHomepageDefaults($name);
            Setting::set(self::SETTING_KEY, $name);
        });
        Setting::clearCache();

        $this->clearViewCache();
    }

    public function deactivate(): void
    {
        Setting::set(self::SETTING_KEY, $this->firstAvailable() ?? self::DEFAULT_THEME);
        $this->clearViewCache();
    }

    public function isAvailable(string $name): bool
    {
        return $this->packageAccess()->allows('theme', $name);
    }

    public function firstAvailable(): ?string
    {
        if (! config('tenancy.enabled')) {
            return self::DEFAULT_THEME;
        }

        return $this->packageAccess()
            ->names('theme')
            ->first(fn (string $name) => $this->exists($name));
    }

    public function delete(string $name): void
    {
        if ($name === self::DEFAULT_THEME) {
            throw new ThemeInstallException('Không thể xóa theme mặc định.');
        }
        if ($name === $this->active()) {
            throw new ThemeInstallException('Không thể xóa theme đang được kích hoạt. Hãy kích hoạt theme khác trước.');
        }

        $dir = $this->themesPath.DIRECTORY_SEPARATOR.$name;
        if (File::isDirectory($dir)) {
            File::deleteDirectory($dir);
        }
        $this->installer->unpublishAssets($name);
        $this->deleteHomepageBlocks($name);
        $this->deletePackagedTheme($name);
        $this->clearPreviewSession($name);
        $this->clearViewCache();
        $this->clearThemeCaches();
    }

    public function deleteFromCatalog(string $name): void
    {
        if ($name === self::DEFAULT_THEME) {
            throw new ThemeInstallException('Không thể xóa theme mặc định.');
        }
        if (! $this->exists($name)) {
            throw new ThemeInstallException("Theme '{$name}' không tồn tại trong catalog.");
        }

        $dir = $this->themesPath.DIRECTORY_SEPARATOR.$name;
        File::deleteDirectory($dir);
        $this->installer->unpublishAssets($name);
        $this->deletePackagedTheme($name);
        $this->clearViewCache();
        $this->clearThemeCaches();
    }

    public function path(string $name): string
    {
        return $this->themesPath.DIRECTORY_SEPARATOR.$name;
    }

    public function viewsPath(string $name): string
    {
        return $this->path($name).DIRECTORY_SEPARATOR.'views';
    }

    public function readMeta(string $dir): ?array
    {
        $file = $dir.DIRECTORY_SEPARATOR.'theme.json';
        if (! File::exists($file)) {
            return null;
        }
        $meta = json_decode(File::get($file), true);
        if (! is_array($meta) || empty($meta['name'])) {
            return null;
        }
        $meta['version'] = $meta['version'] ?? '1.0.0';
        $meta['author'] = $meta['author'] ?? 'Unknown';
        $meta['preview'] = $meta['preview'] ?? null;

        return $meta;
    }

    private function clearViewCache(): void
    {
        try {
            Artisan::call('view:clear');
        } catch (\Throwable $e) {
            // best-effort; view cache will rotate naturally
        }
    }

    private function resetHomepageDefaults(string $theme): void
    {
        $registry = app(WidgetRegistry::class);
        $registry->load($theme);
        $defaults = $registry->defaults();

        DB::transaction(function () use ($theme, $defaults) {
            HomepageBlock::where('theme_name', $theme)->delete();

            foreach ($defaults as $index => $block) {
                HomepageBlock::create($block + [
                    'theme_name' => $theme,
                    'sort_order' => $index + 1,
                    'settings' => [],
                    'is_active' => true,
                ]);
            }
        });
    }

    private function deleteHomepageBlocks(string $name): void
    {
        try {
            HomepageBlock::where('theme_name', $name)->delete();
        } catch (\Throwable $e) {
            // Schema may not exist in early install/test contexts.
        }
    }

    private function deletePackagedTheme(string $name): void
    {
        $packagesPath = $this->themePackagesPath ?: storage_path('app/themes');
        $package = $packagesPath.DIRECTORY_SEPARATOR.$name.'.zip';

        if (File::isFile($package)) {
            File::delete($package);
        }
    }

    private function clearPreviewSession(string $name): void
    {
        try {
            if (session()->get('preview_theme') === $name) {
                session()->forget('preview_theme');
            }
        } catch (\Throwable $e) {
            // Session may be unavailable in CLI contexts.
        }
    }

    private function clearThemeCaches(): void
    {
        try {
            Cache::forget('active_theme');
        } catch (\Throwable $e) {
            // best-effort compatibility with cache stores.
        }
    }

    private function packageAccess(): TenantPackageAccess
    {
        return $this->packages ?? app(TenantPackageAccess::class);
    }
}
