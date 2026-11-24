<?php

namespace App\Http\Middleware;

use App\Models\Plugin;
use App\Tenancy\TenantPackageAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsurePluginActive
{
    public function __construct(private TenantPackageAccess $packages) {}

    public function handle(Request $request, Closure $next, string $plugin): Response
    {
        abort_unless(
            $this->packages->allows('plugin', $plugin)
                && Schema::hasTable('plugins')
                && Plugin::query()->where('name', $plugin)->where('is_active', true)->exists(),
            404,
        );

        return $next($request);
    }
}
