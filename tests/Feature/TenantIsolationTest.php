<?php

namespace Tests\Feature;

use App\Models\Plugin;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\TenantPackage;
use App\Providers\ThemeServiceProvider;
use App\Services\Plugin\PluginManager;
use App\Services\Theme\ThemeManager;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantDatabaseManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    private string $directory;

    private string $betaThemePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = storage_path('framework/testing-tenancy-'.uniqid());
        File::ensureDirectoryExists($this->directory);
        $this->betaThemePath = base_path('themes/tenant-isolation-beta');
        File::ensureDirectoryExists($this->betaThemePath);
        File::put($this->betaThemePath.'/theme.json', json_encode([
            'name' => 'tenant-isolation-beta',
            'version' => '1.0.0',
        ], JSON_THROW_ON_ERROR));
        $central = $this->directory.DIRECTORY_SEPARATOR.'central.sqlite';
        touch($central);

        config([
            'tenancy.enabled' => true,
            'tenancy.central_connection' => 'central',
            'session.driver' => 'database',
            'database.connections.central' => [
                'driver' => 'sqlite',
                'database' => $central,
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('central');

        $exitCode = Artisan::call('migrate', [
            '--database' => 'central',
            '--path' => 'database/migrations/central',
            '--force' => true,
        ]);
        $this->assertSame(0, $exitCode, Artisan::output());
        $this->createTenant('Alpha', 'alpha.test', 'alpha.sqlite', 'Alpha site', 'default', true);
        $this->createTenant('Beta', 'beta.test', 'beta.sqlite', 'Beta site', 'tenant-isolation-beta', false);
        Tenant::query()->where('name', 'Alpha')->firstOrFail()->packages()->create([
            'package_type' => TenantPackage::TYPE_THEME,
            'package_name' => 'tenant-isolation-beta',
        ]);

        Route::middleware('web')->get('/_tenant-probe', function () {
            Storage::disk('public')->put('probe.txt', Setting::get('site_name'));

            return response()->json([
                'tenant' => app(TenantContext::class)->get()->name,
                'site_name' => Setting::get('site_name'),
                'file' => Storage::disk('public')->get('probe.txt'),
                'root' => Storage::disk('public')->path(''),
            ]);
        });

        Route::middleware(['web', 'plugin.active:booking'])
            ->get('/_booking-probe', fn () => response()->json(['active' => true]));

        Route::middleware('web')->get('/_session-probe', function () {
            $previous = session('marker');
            if (request()->filled('set')) {
                session(['marker' => request('set')]);
            }

            return response()->json(['previous' => $previous]);
        });

        Route::middleware('web')->get('/_catalog-probe', fn () => response()->json([
            'themes' => app(ThemeManager::class)->list()->pluck('name')->values(),
            'plugins' => app(PluginManager::class)->list()->pluck('name')->values(),
        ]));

        Route::middleware('web')->get('/_theme-probe/{theme}', function (string $theme) {
            ThemeServiceProvider::bindActiveTheme($theme);

            return response()->json(['theme' => active_theme()]);
        });
    }

    protected function tearDown(): void
    {
        app(TenantDatabaseManager::class)->end();
        DB::purge('tenant');
        DB::purge('central');
        config(['tenancy.enabled' => false]);
        File::deleteDirectory($this->directory);
        File::deleteDirectory($this->betaThemePath);

        parent::tearDown();
    }

    public function test_domain_selects_an_isolated_database_and_storage_root(): void
    {
        $alpha = $this->getJson('http://alpha.test/_tenant-probe')
            ->assertOk()
            ->assertJsonPath('tenant', 'Alpha')
            ->assertJsonPath('site_name', 'Alpha site')
            ->assertJsonPath('file', 'Alpha site')
            ->json();

        $beta = $this->getJson('http://beta.test/_tenant-probe')
            ->assertOk()
            ->assertJsonPath('tenant', 'Beta')
            ->assertJsonPath('site_name', 'Beta site')
            ->assertJsonPath('file', 'Beta site')
            ->json();

        $this->assertNotSame($alpha['root'], $beta['root']);
        $this->assertStringContainsString('tenants', $alpha['root']);
        $this->assertFalse(app(TenantContext::class)->check());
    }

    public function test_plugin_activation_is_scoped_to_the_resolved_tenant(): void
    {
        $this->getJson('http://alpha.test/_booking-probe')
            ->assertOk()
            ->assertJson(['active' => true]);

        $this->getJson('http://beta.test/_booking-probe')
            ->assertNotFound();
    }

    public function test_tenants_only_see_their_assigned_theme_and_plugins(): void
    {
        $this->getJson('http://alpha.test/_catalog-probe')
            ->assertOk()
            ->assertJsonPath('themes', ['default', 'tenant-isolation-beta'])
            ->assertJsonPath('plugins', ['booking']);

        $this->getJson('http://beta.test/_catalog-probe')
            ->assertOk()
            ->assertJsonPath('themes', ['tenant-isolation-beta'])
            ->assertJsonPath('plugins', []);

        $this->getJson('http://alpha.test/_theme-probe/tenant-isolation-beta')
            ->assertOk()
            ->assertJsonPath('theme', 'tenant-isolation-beta');

        $this->getJson('http://beta.test/_theme-probe/default')
            ->assertOk()
            ->assertJsonPath('theme', 'tenant-isolation-beta');
    }

    public function test_same_session_cookie_cannot_cross_tenant_databases(): void
    {
        $cookieName = (string) config('session.cookie');
        $alpha = $this->getJson('http://alpha.test/_session-probe?set=alpha')
            ->assertOk()
            ->assertJsonPath('previous', null);
        $cookie = $alpha->getCookie($cookieName);
        $this->assertNotNull($cookie);

        $this->withCredentials()
            ->withCookie($cookieName, $cookie->getValue())
            ->getJson('http://beta.test/_session-probe?set=beta')
            ->assertOk()
            ->assertJsonPath('previous', null);

        $this->withCookie($cookieName, $cookie->getValue())
            ->getJson('http://alpha.test/_session-probe')
            ->assertOk()
            ->assertJsonPath('previous', 'alpha');
    }

    public function test_unknown_or_disabled_domains_do_not_fall_back_to_another_database(): void
    {
        $this->getJson('http://unknown.test/_tenant-probe')
            ->assertNotFound();
    }

    private function createTenant(
        string $name,
        string $domain,
        string $databaseName,
        string $siteName,
        string $theme,
        bool $bookingActive,
    ): void {
        $database = $this->directory.DIRECTORY_SEPARATOR.$databaseName;
        touch($database);

        $tenant = Tenant::query()->create([
            'uuid' => fake()->uuid(),
            'name' => $name,
            'is_active' => true,
            'db_driver' => 'sqlite',
            'db_database' => $database,
        ]);
        $tenant->domains()->create(['domain' => $domain, 'is_primary' => true]);
        $tenant->packages()->create([
            'package_type' => TenantPackage::TYPE_THEME,
            'package_name' => $theme,
        ]);
        if ($bookingActive) {
            $tenant->packages()->create([
                'package_type' => TenantPackage::TYPE_PLUGIN,
                'package_name' => 'booking',
            ]);
        }

        $databases = app(TenantDatabaseManager::class);
        $databases->initialize($tenant);
        try {
            $exitCode = Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations',
                '--force' => true,
            ]);
            $this->assertSame(0, $exitCode, Artisan::output());

            Setting::set('site_name', $siteName);
            Setting::set('active_theme', $theme);
            Plugin::query()->updateOrCreate(
                ['name' => 'booking'],
                [
                    'title' => 'Booking',
                    'version' => '1.0.0',
                    // Beta deliberately has an active DB record but no central
                    // assignment, proving the entitlement is authoritative.
                    'is_active' => true,
                    'path' => base_path('plugins/booking'),
                    'manifest' => [],
                    'installed_at' => now(),
                ],
            );
        } finally {
            $databases->end();
        }
    }
}
