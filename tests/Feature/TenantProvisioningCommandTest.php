<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\TenantPackage;
use App\Tenancy\TenantDatabaseManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class TenantProvisioningCommandTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = storage_path('framework/testing-provision-'.uniqid());
        File::ensureDirectoryExists($this->directory);
        $central = $this->directory.DIRECTORY_SEPARATOR.'central.sqlite';
        touch($central);

        config([
            'tenancy.enabled' => true,
            'tenancy.central_connection' => 'central',
            'database.connections.central' => [
                'driver' => 'sqlite',
                'database' => $central,
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('central');

        $this->assertSame(0, Artisan::call('migrate', [
            '--database' => 'central',
            '--path' => 'database/migrations/central',
            '--force' => true,
        ]), Artisan::output());
    }

    protected function tearDown(): void
    {
        app(TenantDatabaseManager::class)->end();
        DB::purge('tenant');
        DB::purge('central');
        config(['tenancy.enabled' => false]);
        File::deleteDirectory($this->directory);

        parent::tearDown();
    }

    public function test_create_command_provisions_a_working_tenant(): void
    {
        $tenantDatabase = $this->directory.DIRECTORY_SEPARATOR.'customer.sqlite';

        $output = new BufferedOutput;
        $exitCode = Artisan::call('tenant:create', [
            'name' => 'Customer',
            'domain' => 'customer.test',
            '--alias' => ['www.customer.test'],
            '--driver' => 'sqlite',
            '--database' => $tenantDatabase,
            '--theme' => 'default',
            '--admin-user' => 'owner',
            '--admin-email' => 'owner@customer.test',
            '--admin-password' => 'long-test-password',
        ], $output);

        $this->assertSame(0, $exitCode, $output->fetch());
        $tenant = Tenant::query()->with('domains')->firstOrFail();
        $this->assertTrue($tenant->is_active);
        $this->assertSame(
            ['customer.test', 'www.customer.test'],
            $tenant->domains->pluck('domain')->sort()->values()->all(),
        );
        $this->assertFileExists($tenantDatabase);
        $this->assertDatabaseHas('tenant_packages', [
            'tenant_id' => $tenant->id,
            'package_type' => TenantPackage::TYPE_THEME,
            'package_name' => 'default',
        ], 'central');

        $this->assertSame(0, Artisan::call('tenant:package', [
            'tenant' => 'customer.test',
            'type' => 'plugin',
            'name' => 'default',
        ]), Artisan::output());
        $this->assertDatabaseHas('tenant_packages', [
            'tenant_id' => $tenant->id,
            'package_type' => TenantPackage::TYPE_PLUGIN,
            'package_name' => 'default',
        ], 'central');

        $databases = app(TenantDatabaseManager::class);
        $databases->initialize($tenant);
        try {
            $admin = AdminUser::query()->where('username', 'owner')->firstOrFail();
            $this->assertSame('owner@customer.test', $admin->email);
            $this->assertSame('default', Setting::get('active_theme'));
        } finally {
            $databases->end();
        }
    }

    public function test_registered_disabled_tenant_can_be_migrated_and_activated(): void
    {
        $tenantDatabase = $this->directory.DIRECTORY_SEPARATOR.'pending.sqlite';

        $this->assertSame(0, Artisan::call('tenant:create', [
            'name' => 'Pending',
            'domain' => 'pending.test',
            '--driver' => 'sqlite',
            '--database' => $tenantDatabase,
            '--no-migrate' => true,
        ]), Artisan::output());

        $tenant = Tenant::query()->firstOrFail();
        $this->assertFalse($tenant->is_active);

        $this->assertSame(0, Artisan::call('tenant:migrate', [
            'tenant' => 'pending.test',
            '--activate' => true,
        ]), Artisan::output());

        $this->assertTrue($tenant->refresh()->is_active);
    }
}
