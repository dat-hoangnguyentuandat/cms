<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use App\Services\Admin\RoutePermissionRegistry;
use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    public function __construct(private RoutePermissionRegistry $routePermissions) {}

    public function handle(Request $request, Closure $next)
    {
        if (! session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $user = AdminUser::with('adminRole')->find(session('admin_id'));

        if (! $user || ! $user->is_active) {
            session()->forget(['admin_id', 'admin_user']);

            return redirect()->route('admin.login');
        }

        // Super admin bypasses all permission checks
        if ($user->role !== 'admin') {
            $routeName = $request->route()->getName() ?? '';
            $permKey = $this->routePermissions->resolve($routeName);

            // dashboard is always accessible (redirect target)
            if ($permKey !== null && $permKey !== 'dashboard' && ! $user->hasAdminPermission($permKey)) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'Bạn không có quyền truy cập chức năng này.');
            }
        }

        // Share with all views so sidebar can filter by permission
        view()->share('_adminUser', $user);

        return $next($request);
    }
}
