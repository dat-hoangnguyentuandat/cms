<?php

use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\BootTenantPlugins;
use App\Http\Middleware\EnsurePluginActive;
use App\Http\Middleware\InjectHtmlTags;
use App\Http\Middleware\InjectPluginUi;
use App\Http\Middleware\InjectStructuredData;
use App\Http\Middleware\MaintenanceMode;
use App\Http\Middleware\PlatformAuth;
use App\Http\Middleware\PlatformOnly;
use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\RunTenantPluginMiddleware;
use App\Http\Middleware\ThemeBoot;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.auth' => AdminAuth::class,
            'plugin.active' => EnsurePluginActive::class,
            'platform.auth' => PlatformAuth::class,
            'platform.only' => PlatformOnly::class,
        ]);
        $middleware->web(prepend: [
            ResolveTenant::class,
        ]);
        $middleware->web(append: [
            BootTenantPlugins::class,
            ThemeBoot::class,
            RunTenantPluginMiddleware::class,
            InjectPluginUi::class,
            InjectHtmlTags::class,
            InjectStructuredData::class,
            MaintenanceMode::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
