<?php

namespace App\Tenancy;

use App\Models\Plugin;
use App\Models\Setting;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class TenantHealthChecker
{
    public function __construct(private TenantDatabaseManager $databases) {}

    public function check(Tenant $tenant): array
    {
        $started = microtime(true);

        try {
            $this->databases->initialize($tenant);
            DB::connection()->getPdo();

            $required = ['migrations', 'settings', 'admin_users', 'plugins'];
            $missing = array_values(array_filter($required, fn (string $table) => ! Schema::hasTable($table)));

            return [
                'ok' => $missing === [],
                'latency_ms' => (int) round((microtime(true) - $started) * 1000),
                'missing_tables' => $missing,
                'active_theme' => Schema::hasTable('settings') ? Setting::get('active_theme') : null,
                'active_plugins' => Schema::hasTable('plugins')
                    ? Plugin::query()->where('is_active', true)->pluck('name')->all()
                    : [],
                'error' => null,
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'latency_ms' => (int) round((microtime(true) - $started) * 1000),
                'missing_tables' => [],
                'active_theme' => null,
                'active_plugins' => [],
                'error' => $e->getMessage(),
            ];
        } finally {
            $this->databases->end();
        }
    }
}
