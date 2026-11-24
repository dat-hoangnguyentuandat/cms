<?php

namespace App\Providers;

use App\Services\Theme\ThemeAssetVersion;
use App\Services\Theme\ThemeInstaller;
use App\Services\Theme\ThemeManager;
use App\Services\Theme\ThemeSeeder;
use App\Services\Theme\ThemeWidgetSynchronizer;
use App\Services\Widget\WidgetRegistry;
use App\Tenancy\TenantPackageAccess;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\FileViewFinder;

class ThemeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ThemeAssetVersion::class, fn () => new ThemeAssetVersion);

        $this->app->singleton(ThemeInstaller::class, function ($app) {
            return new ThemeInstaller(
                themesPath: base_path('themes'),
                publicAssetsPath: public_path('theme-assets'),
                assetVersions: $app->make(ThemeAssetVersion::class),
            );
        });

        $this->app->singleton(ThemeManager::class, function ($app) {
            return new ThemeManager(
                installer: $app->make(ThemeInstaller::class),
                themesPath: base_path('themes'),
                themePackagesPath: storage_path('app/themes'),
                packages: $app->make(TenantPackageAccess::class),
            );
        });

        $this->app->singleton(WidgetRegistry::class, function ($app) {
            return new WidgetRegistry(themesPath: base_path('themes'));
        });

        $this->app->singleton(ThemeSeeder::class, function ($app) {
            return new ThemeSeeder;
        });

        $this->app->singleton(ThemeWidgetSynchronizer::class, function ($app) {
            return new ThemeWidgetSynchronizer(
                standardWidgetsPath: public_path('admin-assets/widgets'),
                themesPath: base_path('themes'),
            );
        });
    }

    public function boot(): void
    {
        if (! is_dir(base_path('themes'))) {
            @mkdir(base_path('themes'), 0755, true);
        }
        if (! is_dir(public_path('theme-assets'))) {
            @mkdir(public_path('theme-assets'), 0755, true);
        }
    }

    /**
     * Resolve and bind the active theme into the container for the current request.
     * Called by ThemeBootMiddleware once session is available.
     */
    public static function bindActiveTheme(string $name): void
    {
        $app = app();
        $manager = $app->make(ThemeManager::class);

        if (! $manager->exists($name) || ! $manager->isAvailable($name)) {
            logger()->warning("Theme '{$name}' is missing or unavailable for the current tenant.");
            $name = $manager->firstAvailable();
            abort_unless($name, 503, 'Tenant has no available theme.');
        }

        $themePath = $manager->path($name);
        $viewsPath = $manager->viewsPath($name);

        $app->instance('theme.name', $name);
        $app->instance('theme.path', $themePath);

        // Load widget registry for this theme
        $registry = $app->make(WidgetRegistry::class);
        $registry->load($name);

        if (is_dir($viewsPath)) {
            /** @var FileViewFinder $finder */
            $finder = $app['view']->getFinder();
            $existing = $finder->getPaths();
            // Avoid duplicates if middleware runs twice
            if (! in_array($viewsPath, $existing, true)) {
                $finder->prependLocation($viewsPath);
            }
        }
    }
}
