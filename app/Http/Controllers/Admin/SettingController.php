<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SettingController extends Controller
{
    private function allSettings(): Collection
    {
        return Setting::pluck('setting_value', 'setting_key');
    }

    // Legacy index — kept for any old bookmarks
    public function index()
    {
        return redirect()->route('admin.website-config.general');
    }

    public function infoPage()
    {
        $s = $this->allSettings();

        return view('admin.website-config.thong-tin-website', compact('s'));
    }

    public function updateInfo(Request $request)
    {
        // Handle logo upload
        if ($request->hasFile('site_logo')) {
            $path = $request->file('site_logo')->store('website', 'public');
            Setting::updateOrCreate(['setting_key' => 'site_logo'], ['setting_value' => $path]);
        }

        $this->saveSettings($request);

        return redirect()->route('admin.website-config.info')->with('success', 'Đã lưu thông tin website.');
    }

    public function generalPage()
    {
        $s = $this->allSettings();

        return view('admin.website-config.cau-hinh-chung', compact('s'));
    }

    public function updateGeneral(Request $request)
    {
        $this->saveSettings($request);

        return redirect()->route('admin.website-config.general')->with('success', 'Đã lưu cấu hình chung.');
    }

    public function maintenancePage()
    {
        $s = $this->allSettings();

        return view('admin.website-config.bao-tri-website', compact('s'));
    }

    public function updateMaintenance(Request $request)
    {
        $settings = $request->input('settings', []);
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['setting_key' => $key], ['setting_value' => trim($value)]);
        }
        Setting::clearCache();

        return redirect()->route('admin.website-config.maintenance')->with('success', 'Đã lưu cấu hình bảo trì.');
    }

    public function paginationPage()
    {
        $s = $this->allSettings();

        return view('admin.website-config.cau-hinh-phan-trang', compact('s'));
    }

    public function updatePagination(Request $request)
    {
        $this->saveSettings($request);

        return redirect()->route('admin.website-config.pagination')->with('success', 'Đã lưu cấu hình phân trang.');
    }

    private function saveSettings(Request $request): void
    {
        foreach ($request->input('settings', []) as $key => $value) {
            Setting::updateOrCreate(['setting_key' => $key], ['setting_value' => trim($value)]);
        }
        Setting::clearCache();
    }

    public function updateHotline(Request $request)
    {
        $request->validate([
            'hotline' => 'required|string|max:255',
        ]);

        // Get old value
        $oldValue = Setting::get('hotline', '1900 6789');

        // Update setting
        $newValue = trim($request->input('hotline'));
        Setting::set('hotline', $newValue);

        return response()->json([
            'success' => true,
            'oldValue' => $oldValue,
            'newValue' => $newValue,
        ]);
    }
}
