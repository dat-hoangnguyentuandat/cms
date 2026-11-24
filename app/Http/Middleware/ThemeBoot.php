<?php

namespace App\Http\Middleware;

use App\Providers\ThemeServiceProvider;
use App\Services\Theme\ThemeManager;
use Closure;
use Illuminate\Http\Request;

class ThemeBoot
{
    public function __construct(private ThemeManager $manager) {}

    public function handle(Request $request, Closure $next)
    {
        if ($request->attributes->get('platform_runtime')) {
            return $next($request);
        }

        $name = $this->resolve($request);
        ThemeServiceProvider::bindActiveTheme($name);

        return $next($request);
    }

    /**
     * Priority chain:
     *  1. ?theme=slug (admin only) — also persists to session
     *  2. session('preview_theme') (admin only)
     *  3. env('ACTIVE_THEME')
     *  4. settings.active_theme
     *  5. default
     */
    private function resolve(Request $request): string
    {
        $isAdmin = (bool) $request->session()->get('admin_id');

        if ($isAdmin) {
            $param = $request->query('theme');
            if (is_string($param) && $param !== '') {
                $request->session()->put('preview_theme', $param);

                return $param;
            }
            $sessionTheme = $request->session()->get('preview_theme');
            if (is_string($sessionTheme) && $sessionTheme !== '') {
                return $sessionTheme;
            }
        }

        if (! config('tenancy.enabled')) {
            $envTheme = env('ACTIVE_THEME');
            if (is_string($envTheme) && $envTheme !== '') {
                return $envTheme;
            }
        }

        return $this->manager->active();
    }
}
