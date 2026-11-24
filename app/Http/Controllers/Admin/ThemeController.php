<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ThemeInstallException;
use App\Http\Controllers\Controller;
use App\Services\Theme\ThemeManager;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function __construct(private ThemeManager $manager) {}

    public function index()
    {
        $themes = $this->manager->list();
        $active = $this->manager->active();
        $request = request();
        $q = trim($request->get('q', ''));
        $status = $request->get('status', '');
        $from = $request->get('from', '');
        $to = $request->get('to', '');

        $themes = $themes->filter(function ($theme) use ($q, $status, $from, $to) {
            if ($q !== '' && stripos($theme['name'], $q) === false) {
                return false;
            }
            if ($status !== '') {
                $active = $status === '1';
                if ((bool) ($theme['is_active'] ?? false) !== $active) {
                    return false;
                }
            }
            if ($from !== '' && ! empty($theme['installed']) && date('Y-m-d', $theme['installed']) < $from) {
                return false;
            }
            if ($to !== '' && ! empty($theme['installed']) && date('Y-m-d', $theme['installed']) > $to) {
                return false;
            }

            return true;
        })->values();
        $canManagePackages = ! config('tenancy.enabled') || config('tenancy.allow_package_management');

        return view('admin.themes.index', compact('themes', 'active', 'q', 'status', 'from', 'to', 'canManagePackages'));
    }

    public function upload(Request $request)
    {
        $this->authorizePackageManagement();

        $request->validate([
            'theme' => 'required|file|max:51200', // 50MB
        ]);

        try {
            $meta = $this->manager->install($request->file('theme'));

            return redirect()->route('admin.themes.index')
                ->with('success', "Đã tải theme '{$meta['name']}' thành công.");
        } catch (ThemeInstallException $e) {
            return redirect()->route('admin.themes.index')->with('error', $e->getMessage());
        }
    }

    public function activate(string $name)
    {
        try {
            $this->manager->activate($name);
            // Clear preview override so the activated theme is what shows up next
            session()->forget('preview_theme');

            return redirect()->route('admin.themes.index')
                ->with('success', "Đã kích hoạt theme '{$name}'.");
        } catch (ThemeInstallException $e) {
            return redirect()->route('admin.themes.index')->with('error', $e->getMessage());
        }
    }

    public function destroy(string $name)
    {
        $this->authorizePackageManagement();

        try {
            $this->manager->delete($name);

            return redirect()->route('admin.themes.index')
                ->with('success', "Đã xóa theme '{$name}'.");
        } catch (ThemeInstallException $e) {
            return redirect()->route('admin.themes.index')->with('error', $e->getMessage());
        }
    }

    private function authorizePackageManagement(): void
    {
        abort_if(
            config('tenancy.enabled') && ! config('tenancy.allow_package_management'),
            403,
            'Theme packages are managed centrally in multi-tenant mode.',
        );
    }
}
