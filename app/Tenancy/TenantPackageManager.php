<?php

namespace App\Tenancy;

use App\Models\Plugin;
use App\Models\Tenant;
use App\Models\TenantPackage;
use App\Services\Plugin\PluginManager;
use App\Services\Theme\ThemeManager;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class TenantPackageManager
{
    public function __construct(
        private ThemeManager $themes,
        private PluginManager $plugins,
        private TenantPackageAccess $access,
        private TenantDatabaseManager $databases,
    ) {}

    public function assignTheme(Tenant $tenant, string $name, bool $activate = false): void
    {
        if (! $this->themes->exists($name)) {
            throw new InvalidArgumentException("Theme '{$name}' is not installed in the production catalog.");
        }

        $assignment = $tenant->packages()->firstOrCreate([
            'package_type' => TenantPackage::TYPE_THEME,
            'package_name' => $name,
        ]);
        $this->access->forget();

        if (! $activate) {
            return;
        }

        try {
            $this->databases->initialize($tenant);
            $this->themes->activate($name);
        } catch (Throwable $e) {
            if ($assignment->wasRecentlyCreated) {
                $assignment->delete();
                $this->access->forget();
            }

            throw $e;
        } finally {
            $this->databases->end();
        }
    }

    public function replaceTheme(Tenant $tenant, string $name): void
    {
        $this->assignTheme($tenant, $name, true);
    }

    public function activeTheme(Tenant $tenant): ?string
    {
        try {
            $this->databases->initialize($tenant);

            return $this->themes->active();
        } catch (Throwable) {
            return null;
        } finally {
            $this->databases->end();
        }
    }

    public function assignPlugin(Tenant $tenant, string $name, bool $activate = false): void
    {
        if ($this->plugins->readManifest($this->plugins->path($name)) === null) {
            throw new InvalidArgumentException("Plugin '{$name}' is not installed in the production catalog.");
        }

        $assignment = $tenant->packages()->firstOrCreate([
            'package_type' => TenantPackage::TYPE_PLUGIN,
            'package_name' => $name,
        ]);
        $this->access->forget();

        if (! $activate) {
            return;
        }

        try {
            $this->databases->initialize($tenant);
            $this->plugins->activate($name);
        } catch (Throwable $e) {
            if ($assignment->wasRecentlyCreated) {
                $assignment->delete();
                $this->access->forget();
            }

            throw $e;
        } finally {
            $this->databases->end();
        }
    }

    public function revokePlugin(Tenant $tenant, string $name): void
    {
        $assignment = $tenant->packages()
            ->where('package_type', TenantPackage::TYPE_PLUGIN)
            ->where('package_name', $name)
            ->first();
        if (! $assignment) {
            return;
        }

        try {
            $this->databases->initialize($tenant);
            if (Schema::hasTable('plugins') && Plugin::query()->where('name', $name)->where('is_active', true)->exists()) {
                $this->plugins->deactivate($name);
            }
        } finally {
            $this->databases->end();
        }

        $assignment->delete();
        $this->access->forget();
    }

    public function revokeTheme(Tenant $tenant, string $name): void
    {
        $assignment = $tenant->packages()
            ->where('package_type', TenantPackage::TYPE_THEME)
            ->where('package_name', $name)
            ->first();
        if (! $assignment) {
            return;
        }
        if ($tenant->packages()->where('package_type', TenantPackage::TYPE_THEME)->count() <= 1) {
            throw new RuntimeException('Không thể thu hồi theme duy nhất của tenant.');
        }

        try {
            $this->databases->initialize($tenant);
            if ($this->themes->active() === $name) {
                throw new RuntimeException('Không thể thu hồi theme đang kích hoạt. Hãy kích hoạt theme khác trước.');
            }
        } finally {
            $this->databases->end();
        }

        $assignment->delete();
        $this->access->forget();
    }
}
