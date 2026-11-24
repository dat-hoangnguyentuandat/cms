<?php

namespace App\Tenancy;

use App\Models\Plugin;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class TenantMigrator
{
    public function migrate(): array
    {
        $runs = [];
        $arguments = [
            '--database' => (string) config('tenancy.tenant_connection', 'tenant'),
            '--path' => 'database/migrations',
            '--force' => true,
        ];
        $exitCode = Artisan::call('migrate', $arguments);
        $runs[] = ['path' => 'database/migrations', 'exit_code' => $exitCode, 'output' => Artisan::output()];

        if ($exitCode !== 0 || ! File::isDirectory(base_path('plugins'))) {
            return $runs;
        }

        foreach (Plugin::query()->where('is_active', true)->pluck('name') as $name) {
            $relative = 'plugins/'.$name.'/database/migrations';
            if (! File::isDirectory(base_path($relative))) {
                continue;
            }

            $exitCode = Artisan::call('migrate', [
                '--database' => (string) config('tenancy.tenant_connection', 'tenant'),
                '--path' => $relative,
                '--force' => true,
            ]);
            $runs[] = ['path' => $relative, 'exit_code' => $exitCode, 'output' => Artisan::output()];

            if ($exitCode !== 0) {
                break;
            }
        }

        return $runs;
    }
}
