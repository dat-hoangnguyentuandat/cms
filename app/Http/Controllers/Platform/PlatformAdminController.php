<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformAdmin;
use App\Models\PlatformAuditLog;
use App\Tenancy\PlatformAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class PlatformAdminController extends Controller
{
    public function index(): View
    {
        return view('platform.admins.index', [
            'admins' => PlatformAdmin::query()->orderBy('name')->get(),
            'auditLogs' => PlatformAuditLog::query()
                ->with(['admin', 'tenant'])
                ->latest('created_at')
                ->limit(100)
                ->get(),
        ]);
    }

    public function store(Request $request, PlatformAuditLogger $audit): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._-]+$/'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        $admin = PlatformAdmin::query()->updateOrCreate(
            ['username' => strtolower($validated['username'])],
            [
                'name' => $validated['name'],
                'email' => strtolower($validated['email']),
                'password' => $validated['password'],
                'is_active' => true,
            ],
        );
        $audit->log('platform_admin.saved', metadata: [
            'admin_id' => $admin->id,
            'username' => $admin->username,
            'email' => $admin->email,
        ]);

        return back()->with('success', 'Đã tạo hoặc reset Platform Admin.');
    }

    public function status(
        Request $request,
        PlatformAdmin $platformAdmin,
        PlatformAuditLogger $audit,
    ): RedirectResponse {
        $validated = $request->validate(['is_active' => ['required', 'boolean']]);

        try {
            if (! $validated['is_active'] && request()->attributes->get('platformAdmin')?->is($platformAdmin)) {
                throw new RuntimeException('Không thể khóa tài khoản đang đăng nhập.');
            }
            if (! $validated['is_active'] && PlatformAdmin::query()->where('is_active', true)->count() <= 1) {
                throw new RuntimeException('Không thể khóa Platform Admin đang hoạt động cuối cùng.');
            }
            $platformAdmin->update(['is_active' => (bool) $validated['is_active']]);
            $audit->log('platform_admin.status', metadata: [
                'admin_id' => $platformAdmin->id,
                'email' => $platformAdmin->email,
                'is_active' => $platformAdmin->is_active,
            ]);

            return back()->with('success', 'Đã cập nhật trạng thái Platform Admin.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
