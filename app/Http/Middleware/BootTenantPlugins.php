<?php

namespace App\Http\Middleware;

use App\Services\Plugin\PluginManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BootTenantPlugins
{
    public function __construct(private PluginManager $plugins) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->attributes->get('platform_runtime')) {
            return $next($request);
        }

        $this->plugins->bootActive();

        return $next($request);
    }
}
