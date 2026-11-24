<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\PlatformAdmin;
use App\Models\PlatformAuditLog;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\TenantPackage;
use App\Services\Theme\ThemeManager;
use App\Tenancy\TenantDatabaseManager;
use App\Tenancy\TenantHealthChecker;
use Database\Seeders\ServiceSeeder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use ZipArchive;

class PlatformAdminControlPanelTest extends TestCase
{
    private string $directory;

    private string $catalogPlugin = 'platform-catalog-test';

    private string $catalogTheme = 'platform-catalog-theme-test';

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = storage_path('framework/testing-platform-'.uniqid());
        File::ensureDirectoryExists($this->directory);
        $central = $this->directory.DIRECTORY_SEPARATOR.'central.sqlite';
        touch($central);

        config([
            'tenancy.enabled' => true,
            'tenancy.platform_domain' => 'platform.test',
            'tenancy.central_connection' => 'central',
            'app.url' => 'http://platform.test',
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

        $this->withServerVariables([
            'HTTP_HOST' => 'platform.test',
            'SERVER_NAME' => 'platform.test',
            'HTTPS' => 'off',
        ]);
        $this->withHeader('Host', 'platform.test');
    }

    protected function tearDown(): void
    {
        app(TenantDatabaseManager::class)->end();
        DB::purge('tenant');
        DB::purge('central');
        config([
            'tenancy.enabled' => false,
            'tenancy.platform_domain' => null,
        ]);
        File::deleteDirectory(base_path('plugins/'.$this->catalogPlugin));
        File::deleteDirectory(public_path('plugin-assets/'.$this->catalogPlugin));
        File::deleteDirectory(base_path('themes/'.$this->catalogTheme));
        File::deleteDirectory(public_path('theme-assets/'.$this->catalogTheme));
        File::deleteDirectory($this->directory);

        parent::tearDown();
    }

    public function test_platform_domain_and_central_auth_are_isolated(): void
    {
        $admin = PlatformAdmin::query()->create([
            'name' => 'Operator',
            'username' => 'operator',
            'email' => 'operator@example.com',
            'password' => 'long-platform-password',
            'is_active' => true,
        ]);

        $this->get('http://platform.test/platform/tenants')
            ->assertRedirect('/platform/login');

        $this->post('http://platform.test/platform/login', [
            'username' => $admin->username,
            'password' => 'wrong-password',
        ])->assertRedirect();

        $login = $this->post('http://platform.test/platform/login', [
            'username' => $admin->username,
            'password' => 'long-platform-password',
        ]);
        $login->assertRedirect('/platform/tenants');
        $this->retainPlatformCookie($login);

        $this->get('http://platform.test/platform/tenants')
            ->assertOk()
            ->assertSee('Platform Admin')
            ->assertSee('Tenants');

        $pluginZip = $this->directory.DIRECTORY_SEPARATOR.'catalog-plugin.zip';
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($pluginZip, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('plugin.json', json_encode([
            'name' => $this->catalogPlugin,
            'title' => 'Platform Catalog Test',
            'version' => '1.0.0',
        ], JSON_THROW_ON_ERROR));
        $zip->close();

        $this->post(route('platform.catalog.plugins.upload'), [
            'plugin' => new UploadedFile($pluginZip, 'catalog-plugin.zip', 'application/zip', null, true),
        ])->assertRedirect();
        $this->assertFileExists(base_path('plugins/'.$this->catalogPlugin.'/plugin.json'));
        $this->get(route('platform.catalog.index'))
            ->assertOk()
            ->assertSee('Platform Catalog Test');

        $tenant = Tenant::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Catalog Guard',
            'is_active' => false,
            'db_driver' => 'sqlite',
            'db_database' => ':memory:',
        ]);
        $assignment = $tenant->packages()->create([
            'package_type' => TenantPackage::TYPE_PLUGIN,
            'package_name' => $this->catalogPlugin,
        ]);

        $this->delete(route('platform.catalog.plugins.delete', $this->catalogPlugin))
            ->assertRedirect();
        $this->assertFileExists(base_path('plugins/'.$this->catalogPlugin.'/plugin.json'));

        $assignment->delete();
        $tenant->delete();
        $this->delete(route('platform.catalog.plugins.delete', $this->catalogPlugin))
            ->assertRedirect();
        $this->assertDirectoryDoesNotExist(base_path('plugins/'.$this->catalogPlugin));
        $this->assertTrue(PlatformAuditLog::query()->where('action', 'catalog.plugin_deleted')->exists());

        $themeZip = $this->directory.DIRECTORY_SEPARATOR.'catalog-theme.zip';
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($themeZip, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('theme.json', json_encode([
            'name' => $this->catalogTheme,
            'title' => 'Platform Catalog Theme Test',
            'version' => '1.0.0',
        ], JSON_THROW_ON_ERROR));
        $zip->addFromString('views/home.blade.php', '<h1>Catalog theme</h1>');
        $zip->close();

        $this->post(route('platform.catalog.themes.upload'), [
            'theme' => new UploadedFile($themeZip, 'catalog-theme.zip', 'application/zip', null, true),
        ])->assertRedirect();
        $this->assertFileExists(base_path('themes/'.$this->catalogTheme.'/theme.json'));
        $this->delete(route('platform.catalog.themes.delete', $this->catalogTheme))
            ->assertRedirect();
        $this->assertDirectoryDoesNotExist(base_path('themes/'.$this->catalogTheme));
        $this->assertTrue(PlatformAuditLog::query()->where('action', 'catalog.theme_deleted')->exists());

        $this->get('http://platform.test/')
            ->assertNotFound();

        $this->withServerVariables([
            'HTTP_HOST' => 'customer.test',
            'SERVER_NAME' => 'customer.test',
        ])->withHeader('Host', 'customer.test')->get('http://customer.test/platform/login')->assertNotFound();
    }

    public function test_platform_can_provision_and_operate_a_tenant_end_to_end(): void
    {
        $admin = PlatformAdmin::query()->create([
            'name' => 'Operator',
            'username' => 'operator',
            'email' => 'operator@example.com',
            'password' => 'long-platform-password',
            'is_active' => true,
        ]);
        $login = $this->post('http://platform.test/platform/login', [
            'username' => $admin->username,
            'password' => 'long-platform-password',
        ]);
        $login->assertRedirect('/platform/tenants');
        $this->retainPlatformCookie($login);

        $this->get(route('platform.tenants.create'))
            ->assertOk()
            ->assertViewHas('initialAdmin', fn (array $admin) => $admin['username'] === 'admin'
                && $admin['name'] === 'Administrator'
                && $admin['email'] === 'admin@admin.com'
                && strlen($admin['password']) === 20);

        $extraTheme = app(ThemeManager::class)
            ->catalog()
            ->first(fn (array $theme) => $theme['name'] !== ThemeManager::DEFAULT_THEME);
        $this->assertNotNull($extraTheme);

        $tenantDatabase = $this->directory.DIRECTORY_SEPARATOR.'customer.sqlite';
        $response = $this->post('http://platform.test/platform/tenants', [
            'name' => 'Customer A',
            'primary_domain' => 'customer-a.test',
            'aliases' => "www.customer-a.test\nalias.customer-a.test",
            'db_driver' => 'sqlite',
            'db_database' => $tenantDatabase,
            'theme' => 'default',
            'themes' => [$extraTheme['name']],
            'plugins' => [],
            'admin_user' => 'owner',
            'admin_name' => 'Customer Owner',
            'admin_email' => 'owner@customer-a.test',
            'admin_password' => 'tenant-admin-password',
            'is_active' => '1',
        ]);

        $tenant = Tenant::query()->with(['domains', 'packages'])->firstOrFail();
        $response->assertRedirect(route('platform.tenants.show', $tenant));
        $this->retainPlatformCookie($response);
        $this->assertTrue($tenant->is_active);
        $this->assertSame(3, $tenant->domains->count());
        $this->assertSame(2, $tenant->packages->where('package_type', TenantPackage::TYPE_THEME)->count());
        $this->assertFileExists($tenantDatabase);
        $tenant->update(['db_password' => 'database-secret-visible-test']);

        $this->get(route('platform.tenants.show', $tenant))
            ->assertOk()
            ->assertSee('Customer A')
            ->assertSee('customer-a.test')
            ->assertSee($extraTheme['name'])
            ->assertSee('DatabaseSeeder')
            ->assertSee('PhonixCourseProductSeeder')
            ->assertSee('PhonixSidebarMenuSeeder')
            ->assertDontSee('tenant-admin-password')
            ->assertDontSee('database-secret-visible-test');

        $this->post(route('platform.tenants.package', $tenant), [
            'package_type' => 'theme',
            'package_name' => ThemeManager::DEFAULT_THEME,
            'operation' => 'revoke',
        ])->assertRedirect();
        $this->assertDatabaseHas('tenant_packages', [
            'tenant_id' => $tenant->id,
            'package_type' => TenantPackage::TYPE_THEME,
            'package_name' => ThemeManager::DEFAULT_THEME,
        ], 'central');

        $this->post(route('platform.tenants.package', $tenant), [
            'package_type' => 'theme',
            'package_name' => $extraTheme['name'],
            'operation' => 'revoke',
        ])->assertRedirect();
        $this->assertDatabaseMissing('tenant_packages', [
            'tenant_id' => $tenant->id,
            'package_type' => TenantPackage::TYPE_THEME,
            'package_name' => $extraTheme['name'],
        ], 'central');

        $this->post(route('platform.tenants.package', $tenant), [
            'package_type' => 'theme',
            'package_name' => $extraTheme['name'],
            'operation' => 'assign',
        ])->assertRedirect();
        $this->assertDatabaseHas('tenant_packages', [
            'tenant_id' => $tenant->id,
            'package_type' => TenantPackage::TYPE_THEME,
            'package_name' => $extraTheme['name'],
        ], 'central');

        $this->post('http://platform.test/platform/tenants', [
            'name' => 'Conflicting Customer',
            'primary_domain' => 'customer-a.test',
            'db_driver' => 'sqlite',
            'db_database' => $this->directory.DIRECTORY_SEPARATOR.'conflict.sqlite',
            'theme' => 'default',
            'is_active' => '1',
        ])->assertRedirect();
        $this->assertSame(1, Tenant::query()->count());

        $this->post(route('platform.tenants.package', $tenant), [
            'package_type' => 'plugin',
            'package_name' => 'default',
            'operation' => 'assign',
        ])->assertRedirect();
        $this->assertDatabaseHas('tenant_packages', [
            'tenant_id' => $tenant->id,
            'package_type' => TenantPackage::TYPE_PLUGIN,
            'package_name' => 'default',
        ], 'central');

        $this->post(route('platform.tenants.health', $tenant))
            ->assertRedirect();
        $this->assertTrue(app(TenantHealthChecker::class)->check($tenant)['ok']);

        Route::middleware('web')->get('/platform/_session-runtime/{tenant}', function (
            Request $request,
            Tenant $tenant,
            TenantHealthChecker $checker,
        ) {
            $checker->check($tenant);
            session()->flash('runtime_probe', 'preserved');

            return response()->json([
                'flash_visible' => $request->session()->get('runtime_probe'),
            ]);
        });
        $this->get('http://platform.test/platform/_session-runtime/'.$tenant->uuid)
            ->assertOk()
            ->assertJson([
                'flash_visible' => 'preserved',
            ]);

        $this->post(route('platform.tenants.migrate', $tenant))
            ->assertRedirect();
        $this->post(route('platform.tenants.cache', $tenant))
            ->assertRedirect();

        $this->post(route('platform.tenants.status', $tenant), ['is_active' => '0'])
            ->assertRedirect();
        $this->assertFalse($tenant->refresh()->is_active);

        $this->put(route('platform.tenants.update', $tenant), [
            'name' => 'Customer A Updated',
            'primary_domain' => 'new.customer-a.test',
            'aliases' => 'www.new.customer-a.test',
            'db_driver' => 'sqlite',
            'db_database' => $tenantDatabase,
            'db_password' => '',
        ])->assertRedirect(route('platform.tenants.show', $tenant));

        $tenant->refresh()->load('domains');
        $this->assertSame('Customer A Updated', $tenant->name);
        $this->assertSame(
            ['new.customer-a.test', 'www.new.customer-a.test'],
            $tenant->domains->pluck('domain')->sort()->values()->all(),
        );

        $this->get(route('platform.catalog.index'))
            ->assertOk()
            ->assertSee('Catalog dùng chung')
            ->assertSee('default');
        $this->get(route('platform.admins.index'))
            ->assertOk()
            ->assertSee('Platform Admins');

        $this->post(route('platform.tenants.admins.store', $tenant), [
            'username' => 'support',
            'full_name' => 'Support Admin',
            'email' => 'support@customer-a.test',
            'password' => 'support-password-2026',
            'password_confirmation' => 'support-password-2026',
        ])->assertRedirect();

        $databases = app(TenantDatabaseManager::class);
        $databases->initialize($tenant);
        try {
            $support = AdminUser::query()->where('username', 'support')->firstOrFail();
            $supportId = $support->id;
            $supportPassword = $support->password;
        } finally {
            $databases->end();
        }

        $this->get(route('platform.tenants.show', [$tenant, 'edit_admin' => $supportId]))
            ->assertOk()
            ->assertSee('Lưu thay đổi')
            ->assertSee('support@customer-a.test');

        $this->put(route('platform.tenants.admins.update', [$tenant, $supportId]), [
            'username' => 'support',
            'full_name' => 'Support Admin',
            'email' => 'updated-support@customer-a.test',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('platform.tenants.show', $tenant));

        $databases->initialize($tenant);
        try {
            $support = AdminUser::query()->findOrFail($supportId);
            $this->assertSame('updated-support@customer-a.test', $support->email);
            $this->assertSame($supportPassword, $support->password);
            $this->assertTrue(Hash::check('support-password-2026', $support->password));
        } finally {
            $databases->end();
        }

        $this->post(route('platform.tenants.admins.status', [$tenant, $supportId]), [
            'is_active' => '0',
        ])->assertRedirect();
        $this->post(route('platform.tenants.seed', $tenant), [
            'seeder' => ServiceSeeder::class,
            'confirmation' => $tenant->name,
        ])->assertRedirect();

        $databases->initialize($tenant);
        try {
            $this->assertFalse(AdminUser::query()->findOrFail($supportId)->is_active);
            $this->assertTrue(Service::query()->where('slug', 'massage-thu-gian-toan-than')->exists());
        } finally {
            $databases->end();
        }

        $this->post(route('platform.admins.store'), [
            'name' => 'Backup Operator',
            'username' => 'backup',
            'email' => 'backup@example.com',
            'password' => 'backup-platform-password',
            'password_confirmation' => 'backup-platform-password',
        ])->assertRedirect();
        $backup = PlatformAdmin::query()->where('email', 'backup@example.com')->firstOrFail();
        $this->post(route('platform.admins.status', $backup), ['is_active' => '0'])
            ->assertRedirect();
        $this->assertFalse($backup->refresh()->is_active);

        $this->assertTrue(PlatformAuditLog::query()->where('tenant_id', $tenant->id)->where('action', 'tenant.seed')->exists());
        $this->assertTrue(PlatformAuditLog::query()->where('action', 'tenant.admin_created')->exists());
        $this->assertTrue(PlatformAuditLog::query()->where('action', 'tenant.admin_updated')->exists());
        $this->assertTrue(PlatformAuditLog::query()->where('action', 'platform_admin.saved')->exists());
    }

    public function test_platform_admin_command_has_no_default_password(): void
    {
        $this->assertSame(1, Artisan::call('platform:admin', [
            'username' => 'operator',
        ]));

        $this->assertSame(0, Artisan::call('platform:admin', [
            'username' => 'operator',
            '--email' => 'operator@example.com',
            '--name' => 'Operator',
            '--password' => 'long-platform-password',
        ]), Artisan::output());

        $this->assertDatabaseHas('platform_admins', [
            'username' => 'operator',
            'email' => 'operator@example.com',
            'is_active' => true,
        ], 'central');
    }

    private function retainPlatformCookie(TestResponse $response): void
    {
        $name = Str::slug((string) config('app.name')).'_platform_session';
        $cookies = collect($response->headers->getCookies())->keyBy(fn ($cookie) => $cookie->getName());
        $this->assertTrue($cookies->has($name));

        foreach ($cookies as $cookieName => $cookie) {
            $this->withUnencryptedCookie($cookieName, $cookie->getValue());
        }
    }
}
