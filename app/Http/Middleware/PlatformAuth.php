<?php

namespace App\Http\Middleware;

use App\Models\PlatformAdmin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlatformAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminId = $request->session()->get('platform_admin_id');
        $admin = $adminId
            ? PlatformAdmin::query()->whereKey($adminId)->where('is_active', true)->first()
            : null;

        if (! $admin) {
            $request->session()->forget('platform_admin_id');

            return redirect()->route('platform.login');
        }

        $request->attributes->set('platformAdmin', $admin);
        app()->instance('platform.admin', $admin);

        return $next($request);
    }
}
