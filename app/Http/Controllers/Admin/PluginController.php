<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\PluginException;
use App\Http\Controllers\Controller;
use App\Models\PluginSetting;
use App\Services\Plugin\PluginManager;
use Illuminate\Http\Request;

class PluginController extends Controller
{
    public function __construct(private PluginManager $manager) {}

    public function index()
    {
        $request = request();
        $q = trim($request->get('q', ''));
        $status = $request->get('status', '');

        $plugins = $this->manager->list()
            ->filter(function (array $plugin) use ($q, $status) {
                if ($q !== '' && stripos($plugin['title'].' '.$plugin['name'], $q) === false) {
                    return false;
                }
                if ($status !== '') {
                    return (bool) $plugin['is_active'] === ($status === '1');
                }

                return true;
            })
            ->values();
        $canManagePackages = ! config('tenancy.enabled') || config('tenancy.allow_package_management');

        return view('admin.plugins.index', compact('plugins', 'q', 'status', 'canManagePackages'));
    }

    public function upload(Request $request)
    {
        $this->authorizePackageManagement();

        $request->validate([
            'plugin' => 'required|file|max:51200',
        ]);

        try {
            $manifest = $this->manager->install($request->file('plugin'));

            return redirect()->route('admin.plugins.index')
                ->with('success', "Đã cài/cập nhật plugin '{$manifest['title']}'.");
        } catch (PluginException $e) {
            return redirect()->route('admin.plugins.index')->with('error', $e->getMessage());
        }
    }

    public function activate(string $name)
    {
        try {
            $this->manager->activate($name);

            return redirect()->route('admin.plugins.index')->with('success', "Đã kích hoạt plugin '{$name}'.");
        } catch (PluginException $e) {
            return redirect()->route('admin.plugins.index')->with('error', $e->getMessage());
        }
    }

    public function deactivate(string $name)
    {
        $this->manager->deactivate($name);

        return redirect()->route('admin.plugins.index')->with('success', "Đã tắt plugin '{$name}'.");
    }

    public function settings(string $name)
    {
        $plugin = $this->manager->list()->firstWhere('name', $name);
        abort_unless($plugin, 404);

        $schema = $plugin['manifest']['settings_schema'] ?? $plugin['manifest']['settings'] ?? [];
        $values = PluginSetting::where('plugin_name', $name)->pluck('setting_value', 'setting_key')->toArray();

        return view('admin.plugins.settings', compact('plugin', 'schema', 'values'));
    }

    public function updateSettings(Request $request, string $name)
    {
        $plugin = $this->manager->list()->firstWhere('name', $name);
        abort_unless($plugin, 404);

        $schema = $plugin['manifest']['settings_schema'] ?? $plugin['manifest']['settings'] ?? [];
        $fields = $schema['fields'] ?? $schema;

        foreach ($fields as $key => $field) {
            $type = $field['type'] ?? 'text';
            $value = $type === 'checkbox'
                ? ($request->boolean("settings.$key") ? '1' : '0')
                : $request->input("settings.$key", '');

            PluginSetting::set($name, $key, $value);
        }

        return redirect()->route('admin.plugins.settings', $name)->with('success', 'Đã lưu cấu hình plugin.');
    }

    public function destroy(string $name)
    {
        $this->authorizePackageManagement();

        try {
            $this->manager->delete($name);

            return redirect()->route('admin.plugins.index')->with('success', "Đã gỡ plugin '{$name}'.");
        } catch (PluginException $e) {
            return redirect()->route('admin.plugins.index')->with('error', $e->getMessage());
        }
    }

    private function authorizePackageManagement(): void
    {
        abort_if(
            config('tenancy.enabled') && ! config('tenancy.allow_package_management'),
            403,
            'Plugin packages are managed centrally in multi-tenant mode.',
        );
    }
}
