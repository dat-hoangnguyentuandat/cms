<?php

namespace App\Http\Controllers\Platform;

use App\Exceptions\PluginException;
use App\Exceptions\ThemeInstallException;
use App\Http\Controllers\Controller;
use App\Models\TenantPackage;
use App\Services\Plugin\PluginManager;
use App\Services\Theme\ThemeManager;
use App\Tenancy\PlatformAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class CatalogController extends Controller
{
    public function index(ThemeManager $themes, PluginManager $plugins): View
    {
        $usage = TenantPackage::query()
            ->selectRaw('package_type, package_name, count(*) as tenant_count')
            ->groupBy('package_type', 'package_name')
            ->get()
            ->keyBy(fn (TenantPackage $package) => $package->package_type.':'.$package->package_name);

        return view('platform.catalog.index', [
            'themes' => $themes->catalog()->sortBy('name')->values(),
            'plugins' => $plugins->catalog()->sortBy('name')->values(),
            'usage' => $usage,
        ]);
    }

    public function uploadTheme(
        Request $request,
        ThemeManager $themes,
        PlatformAuditLogger $audit,
    ): RedirectResponse {
        $request->validate(['theme' => ['required', 'file', 'mimes:zip', 'max:51200']]);

        try {
            $meta = $themes->install($request->file('theme'));
            $audit->log('catalog.theme_uploaded', metadata: [
                'name' => $meta['name'],
                'version' => $meta['version'] ?? null,
            ]);

            return back()->with('success', "Đã cài/cập nhật theme '{$meta['name']}'.");
        } catch (ThemeInstallException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function uploadPlugin(
        Request $request,
        PluginManager $plugins,
        PlatformAuditLogger $audit,
    ): RedirectResponse {
        $request->validate(['plugin' => ['required', 'file', 'mimes:zip', 'max:51200']]);

        try {
            $manifest = $plugins->install($request->file('plugin'));
            $audit->log('catalog.plugin_uploaded', metadata: [
                'name' => $manifest['name'],
                'version' => $manifest['version'] ?? null,
            ]);

            return back()->with('success', "Đã cài/cập nhật plugin '{$manifest['name']}'.");
        } catch (PluginException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function deleteTheme(
        string $name,
        ThemeManager $themes,
        PlatformAuditLogger $audit,
    ): RedirectResponse {
        try {
            $this->assertUnused(TenantPackage::TYPE_THEME, $name);
            $themes->deleteFromCatalog($name);
            $audit->log('catalog.theme_deleted', metadata: ['name' => $name]);

            return back()->with('success', "Đã xóa theme '{$name}' khỏi catalog.");
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function deletePlugin(
        string $name,
        PluginManager $plugins,
        PlatformAuditLogger $audit,
    ): RedirectResponse {
        try {
            $this->assertUnused(TenantPackage::TYPE_PLUGIN, $name);
            $plugins->deleteFromCatalog($name);
            $audit->log('catalog.plugin_deleted', metadata: ['name' => $name]);

            return back()->with('success', "Đã xóa plugin '{$name}' khỏi catalog.");
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function assertUnused(string $type, string $name): void
    {
        $count = TenantPackage::query()
            ->where('package_type', $type)
            ->where('package_name', $name)
            ->count();

        if ($count > 0) {
            throw new RuntimeException("Không thể xóa '{$name}' vì đang được cấp cho {$count} tenant.");
        }
    }
}
