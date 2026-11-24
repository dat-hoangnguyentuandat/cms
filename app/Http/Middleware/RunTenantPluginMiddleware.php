<?php

namespace App\Http\Middleware;

use App\Services\Plugin\PluginManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Symfony\Component\HttpFoundation\Response;

class RunTenantPluginMiddleware
{
    public function __construct(private PluginManager $plugins) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->attributes->get('platform_runtime')) {
            return $next($request);
        }

        $middleware = $this->plugins->activeMiddleware();
        if ($middleware === []) {
            return $next($request);
        }

        return app(Pipeline::class)
            ->send($request)
            ->through($middleware)
            ->then($next);
    }
}
