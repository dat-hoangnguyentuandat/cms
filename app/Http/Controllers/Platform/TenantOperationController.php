<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Tenancy\PlatformAuditLogger;
use App\Tenancy\TenantMaintenanceManager;
use App\Tenancy\TenantSeederCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class TenantOperationController extends Controller
{
    public function clearCache(
        Tenant $tenant,
        TenantMaintenanceManager $maintenance,
        PlatformAuditLogger $audit,
    ): RedirectResponse {
        try {
            $maintenance->clearCache($tenant);
            $audit->log('tenant.cache_cleared', $tenant);

            return back()->with('success', 'Đã xóa cache riêng của tenant.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function seed(
        Request $request,
        Tenant $tenant,
        TenantMaintenanceManager $maintenance,
        TenantSeederCatalog $seeders,
        PlatformAuditLogger $audit,
    ): RedirectResponse {
        $validated = $request->validate([
            'seeder' => ['required', 'string', Rule::in($seeders->all())],
            'confirmation' => ['required', 'string'],
        ]);
        if (! hash_equals($tenant->name, $validated['confirmation'])) {
            return back()->withErrors(['confirmation' => 'Tên tenant xác nhận không chính xác.']);
        }

        try {
            $output = $maintenance->seed($tenant, $validated['seeder']);
            $audit->log('tenant.seed', $tenant, [
                'seeder' => $validated['seeder'],
                'output' => mb_substr($output, 0, 2000),
            ]);

            return back()->with('success', 'Seeder đã chạy thành công trên database tenant.');
        } catch (Throwable $e) {
            $audit->log('tenant.seed_failed', $tenant, [
                'seeder' => $validated['seeder'],
                'error' => mb_substr($e->getMessage(), 0, 1000),
            ]);

            return back()->with('error', $e->getMessage());
        }
    }

    public function saveAdministrator(
        Request $request,
        Tenant $tenant,
        TenantMaintenanceManager $maintenance,
        PlatformAuditLogger $audit,
    ): RedirectResponse {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._-]+$/'],
            'full_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        try {
            $maintenance->createAdministrator($tenant, $validated);
            $audit->log('tenant.admin_created', $tenant, ['username' => $validated['username']]);

            return back()->with('success', 'Đã tạo tài khoản admin tenant.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateAdministrator(
        Request $request,
        Tenant $tenant,
        int $adminId,
        TenantMaintenanceManager $maintenance,
        PlatformAuditLogger $audit,
    ): RedirectResponse {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._-]+$/'],
            'full_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'password' => ['nullable', 'string', 'min:12', 'confirmed'],
        ]);

        try {
            $username = $maintenance->updateAdministrator($tenant, $adminId, $validated);
            $audit->log('tenant.admin_updated', $tenant, [
                'username' => $username,
                'password_changed' => filled($validated['password'] ?? null),
            ]);

            return redirect()
                ->route('platform.tenants.show', $tenant)
                ->with('success', 'Đã cập nhật tài khoản admin tenant.');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function administratorStatus(
        Request $request,
        Tenant $tenant,
        int $adminId,
        TenantMaintenanceManager $maintenance,
        PlatformAuditLogger $audit,
    ): RedirectResponse {
        $validated = $request->validate(['is_active' => ['required', 'boolean']]);

        try {
            $username = $maintenance->setAdministratorStatus(
                $tenant,
                $adminId,
                (bool) $validated['is_active'],
            );
            $audit->log('tenant.admin_status', $tenant, [
                'username' => $username,
                'is_active' => (bool) $validated['is_active'],
            ]);

            return back()->with('success', 'Đã cập nhật trạng thái admin tenant.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
