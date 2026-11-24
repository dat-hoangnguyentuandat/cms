<?php

namespace App\Tenancy;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PlatformRuntimeManager
{
    private ?array $original = null;

    public function __construct(private Application $app) {}

    public function initialize(): void
    {
        if ($this->original !== null) {
            return;
        }

        foreach (['session.driver', 'session.cookie', 'session.connection', 'session.store', 'cache.default'] as $key) {
            $this->original[$key] = config($key);
        }

        config([
            'session.driver' => 'cookie',
            'session.cookie' => Str::slug((string) config('app.name', 'cms')).'_platform_session',
            'session.connection' => null,
            'session.store' => null,
            'cache.default' => 'array',
        ]);

        $this->forgetSessionDriver();
        $this->forgetCacheDriver();
    }

    public function end(): void
    {
        if ($this->original === null) {
            return;
        }

        config($this->original);
        $this->forgetSessionDriver();
        $this->forgetCacheDriver();
        $this->original = null;
    }

    private function forgetSessionDriver(): void
    {
        if ($this->app->bound('session')) {
            $this->app->make('session')->forgetDrivers();
        }

        $this->app->forgetInstance('session.store');
    }

    private function forgetCacheDriver(): void
    {
        if ($this->app->bound('cache')) {
            Cache::forgetDriver((string) config('cache.default'));
        }
    }
}
