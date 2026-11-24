<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->attributes->get('platform_runtime')) {
            return $next($request);
        }

        // Skip for admin routes and logged-in admin sessions
        if ($request->is('admin*') || session('admin_id')) {
            return $next($request);
        }

        if (Setting::get('maintenance_mode', '0') === '1') {
            $message = Setting::get('maintenance_message', '<p>Website đang trong quá trình bảo trì. Vui lòng quay lại sau.</p>');

            return response(view('maintenance', compact('message')), 503);
        }

        return $next($request);
    }
}
