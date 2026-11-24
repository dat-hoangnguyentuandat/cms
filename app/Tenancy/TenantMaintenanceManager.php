<?php

namespace App\Tenancy;

use App\Models\AdminUser;
use App\Models\Setting;
use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class TenantMaintenanceManager
{
    public function __construct(
        private TenantDatabaseManager $databases,
        private TenantSeederCatalog $seeders,
    ) {}

    public function seed(Tenant $tenant, string $seeder): string
    {
        if (! $this->seeders->contains($seeder)) {
            throw new InvalidArgumentException('Seeder không tồn tại trong database/seeders.');
        }

        try {
            $this->databases->initialize($tenant);
            $exitCode = Artisan::call('db:seed', [
                '--database' => (string) config('tenancy.tenant_connection', 'tenant'),
                '--class' => $seeder,
                '--force' => true,
            ]);
            if ($exitCode !== 0) {
                throw new RuntimeException(trim(Artisan::output()) ?: 'Seeder chạy thất bại.');
            }

            return trim(Artisan::output());
        } finally {
            $this->databases->end();
        }
    }

    public function clearCache(Tenant $tenant): void
    {
        try {
            $this->databases->initialize($tenant);
            $store = (string) config('cache.default');
            $driver = (string) config("cache.stores.{$store}.driver");
            if (! in_array($driver, ['array', 'database', 'file'], true)) {
                throw new RuntimeException(
                    "Không thể flush cache driver '{$driver}' an toàn theo tenant. Hãy dùng cache store có vùng lưu trữ riêng.",
                );
            }

            Cache::store($store)->flush();
            Setting::clearCache();
        } finally {
            $this->databases->end();
        }
    }

    public function administrators(Tenant $tenant): array
    {
        try {
            $this->databases->initialize($tenant);

            return [
                'ok' => true,
                'items' => AdminUser::query()
                    ->orderBy('full_name')
                    ->get(['id', 'username', 'full_name', 'email', 'role', 'is_active', 'last_login'])
                    ->map(fn (AdminUser $admin) => [
                        'id' => $admin->id,
                        'username' => $admin->username,
                        'full_name' => $admin->full_name,
                        'email' => $admin->email,
                        'role' => $admin->role,
                        'is_active' => $admin->is_active,
                        'last_login' => $admin->last_login?->toDateTimeString(),
                    ])
                    ->all(),
                'error' => null,
            ];
        } catch (Throwable $e) {
            return ['ok' => false, 'items' => [], 'error' => $e->getMessage()];
        } finally {
            $this->databases->end();
        }
    }

    public function createAdministrator(Tenant $tenant, array $data): void
    {
        try {
            $this->databases->initialize($tenant);
            if (AdminUser::query()->where('username', $data['username'])->exists()) {
                throw new RuntimeException('Username đã tồn tại trong tenant.');
            }

            AdminUser::query()->create([
                'username' => $data['username'],
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'admin',
                'is_active' => true,
            ]);
        } finally {
            $this->databases->end();
        }
    }

    public function updateAdministrator(Tenant $tenant, int $adminId, array $data): string
    {
        try {
            $this->databases->initialize($tenant);
            $admin = AdminUser::query()->findOrFail($adminId);
            if (AdminUser::query()
                ->where('username', $data['username'])
                ->where($admin->getKeyName(), '!=', $admin->getKey())
                ->exists()) {
                throw new RuntimeException('Username đã thuộc tài khoản khác trong tenant.');
            }

            $updates = [
                'username' => $data['username'],
                'full_name' => $data['full_name'],
                'email' => $data['email'],
            ];
            if (! empty($data['password'])) {
                $updates['password'] = Hash::make($data['password']);
            }

            $admin->update($updates);

            return $admin->username;
        } finally {
            $this->databases->end();
        }
    }

    public function setAdministratorStatus(Tenant $tenant, int $adminId, bool $active): string
    {
        try {
            $this->databases->initialize($tenant);
            $admin = AdminUser::query()->findOrFail($adminId);
            if (! $active && AdminUser::query()->where('is_active', true)->count() <= 1) {
                throw new RuntimeException('Không thể khóa admin đang hoạt động cuối cùng của tenant.');
            }
            $admin->update(['is_active' => $active]);

            return $admin->username;
        } finally {
            $this->databases->end();
        }
    }
}
