<?php

namespace Tests\Feature;

use App\Exceptions\ThemeInstallException;
use App\Http\Middleware\ThemeBoot;
use App\Models\HomepageBlock;
use App\Models\Faq;
use App\Models\LocalBusiness;
use App\Models\MenuItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Providers\ThemeServiceProvider;
use App\Services\Theme\ThemeInstaller;
use App\Services\Theme\ThemeManager;
use App\Services\Theme\ThemeAssetVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class ThemeSystemTest extends TestCase
{
    use RefreshDatabase;

    private string $themesPath;

    private string $publicAssetsPath;

    private string $themePackagesPath;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::clearCache();

        // Use isolated paths under storage so tests don't pollute project themes/
        $this->themesPath = storage_path('app/test-themes');
        $this->publicAssetsPath = storage_path('app/test-theme-assets');
        $this->themePackagesPath = storage_path('app/test-theme-packages');

        File::ensureDirectoryExists($this->themesPath);
        File::ensureDirectoryExists($this->publicAssetsPath);
        File::ensureDirectoryExists($this->themePackagesPath);

        // Re-bind services to test paths
        $this->app->singleton(ThemeInstaller::class, function () {
            return new ThemeInstaller(
                themesPath: $this->themesPath,
                publicAssetsPath: $this->publicAssetsPath,
                assetVersions: app(ThemeAssetVersion::class),
            );
        });
        $this->app->singleton(ThemeManager::class, function ($app) {
            return new ThemeManager(
                installer: $app->make(ThemeInstaller::class),
                themesPath: $this->themesPath,
                themePackagesPath: $this->themePackagesPath,
            );
        });
    }

    protected function tearDown(): void
    {
        Setting::clearCache();
        File::deleteDirectory($this->themesPath);
        File::deleteDirectory($this->publicAssetsPath);
        File::deleteDirectory($this->themePackagesPath);
        parent::tearDown();
    }

    private function normalize(string $path): string
    {
        return str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
    }

    private function makeZip(array $meta = ['name' => 'sample', 'version' => '1.0.0'], bool $withViews = true): UploadedFile
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'theme-zip-').'.zip';
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('theme.json', json_encode($meta));
        if ($withViews) {
            $zip->addFromString('views/layouts/app.blade.php', '<html>{{ $slot ?? "" }}</html>');
            $zip->addFromString('views/home.blade.php', '@extends("layouts.app")@section("content")sample home@endsection');
        }
        $zip->addFromString('assets/css/style.css', 'body{color:red}');
        $zip->close();

        return new UploadedFile($zipPath, 'sample.zip', 'application/zip', null, true);
    }

    public function test_install_valid_zip_creates_theme_folder_and_published_assets(): void
    {
        $manager = app(ThemeManager::class);

        $meta = $manager->install($this->makeZip(['name' => 'sample', 'version' => '2.0.0']));

        $this->assertSame('sample', $meta['name']);
        $this->assertDirectoryExists($this->themesPath.'/sample/views');
        $this->assertFileExists($this->themesPath.'/sample/theme.json');
        $this->assertFileExists($this->publicAssetsPath.'/sample/css/style.css');
        $this->assertNotSame('1', app(ThemeAssetVersion::class)->current('sample'));
    }

    public function test_reinstall_generates_a_new_asset_release_version(): void
    {
        $manager = app(ThemeManager::class);
        $manager->install($this->makeZip(['name' => 'sample', 'version' => '1.0.0']));
        $firstVersion = app(ThemeAssetVersion::class)->current('sample');

        $manager->install($this->makeZip(['name' => 'sample', 'version' => '1.0.1']));
        $secondVersion = app(ThemeAssetVersion::class)->current('sample');

        $this->assertNotSame($firstVersion, $secondVersion);
        $this->app->instance('theme.name', 'sample');
        $this->assertStringEndsWith('?v='.$secondVersion, versioned_theme_asset('css/style.css'));
    }

    public function test_install_invalid_zip_throws(): void
    {
        $manager = app(ThemeManager::class);

        $this->expectException(ThemeInstallException::class);

        $zipPath = tempnam(sys_get_temp_dir(), 'bad-zip-').'.zip';
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('not-a-theme.txt', 'oops');
        $zip->close();

        $manager->install(new UploadedFile($zipPath, 'bad.zip', 'application/zip', null, true));
    }

    public function test_activate_persists_setting_and_view_path_is_resolvable(): void
    {
        $manager = app(ThemeManager::class);
        $manager->install($this->makeZip(['name' => 'sample']));

        $manager->activate('sample');

        $this->assertSame('sample', Setting::get(ThemeManager::SETTING_KEY));
        $this->assertTrue($manager->exists('sample'));

        ThemeServiceProvider::bindActiveTheme('sample');
        $this->assertSame('sample', app('theme.name'));

        $expected = $this->normalize($manager->viewsPath('sample'));
        $registered = array_map([$this, 'normalize'], $this->app['view']->getFinder()->getPaths());
        $this->assertContains($expected, $registered);
    }

    public function test_activate_replaces_old_menu_with_themes_declared_menu(): void
    {
        $themePath = $this->themesPath.'/garage';
        File::ensureDirectoryExists($themePath);
        File::put($themePath.'/theme.json', json_encode(['name' => 'garage', 'version' => '1.0.0']));
        File::put($themePath.'/seed.php', '<?php return '.var_export([
            'settings' => ['site_name' => 'Garage mới'],
            'menu_items' => [
                ['label' => 'Trang chủ', 'url' => '/', 'type' => 'custom'],
                ['label' => 'Đánh giá', 'url' => '/danh-gia', 'type' => 'custom'],
            ],
        ], true).';');

        $oldParent = MenuItem::create([
            'label' => 'Dịch vụ cũ', 'url' => '/dich-vu-cu', 'type' => 'custom',
            'sort_order' => 1, 'is_active' => true,
        ]);
        MenuItem::create([
            'parent_id' => $oldParent->id, 'label' => 'Menu con cũ',
            'url' => '/menu-con-cu', 'type' => 'custom', 'sort_order' => 1, 'is_active' => true,
        ]);
        Product::create(['name' => 'Sản phẩm theme cũ', 'slug' => 'san-pham-theme-cu']);
        Testimonial::create([
            'name' => 'Đánh giá Google cũ', 'content' => 'Nội dung cũ',
            'source' => 'google', 'source_id' => 'old-review',
        ]);
        Faq::create(['question' => 'FAQ theme cũ?', 'answer' => 'Nội dung cũ']);
        LocalBusiness::create(['name' => 'Doanh nghiệp theme cũ', 'is_primary' => true]);
        Setting::set('google_maps_url', 'https://maps.example/old-business');
        Setting::set('maintenance_mode', '1');
        HomepageBlock::create([
            'theme_name' => 'garage', 'zone' => 'main', 'block_type' => 'editor',
            'title' => 'Block từ ZIP cũ', 'settings' => [], 'sort_order' => 1, 'is_active' => true,
        ]);

        app(ThemeManager::class)->activate('garage');

        $this->assertSame(['/', '/danh-gia'], MenuItem::orderBy('sort_order')->pluck('url')->all());
        $this->assertDatabaseMissing('menu_items', ['url' => '/dich-vu-cu']);
        $this->assertDatabaseMissing('menu_items', ['url' => '/menu-con-cu']);
        $this->assertDatabaseMissing('products', ['slug' => 'san-pham-theme-cu']);
        $this->assertDatabaseMissing('testimonials', ['source_id' => 'old-review']);
        $this->assertDatabaseMissing('faqs', ['question' => 'FAQ theme cũ?']);
        $this->assertDatabaseMissing('local_businesses', ['name' => 'Doanh nghiệp theme cũ']);
        $this->assertNull(Setting::get('google_maps_url'));
        $this->assertSame('1', Setting::get('maintenance_mode'));
        $this->assertDatabaseMissing('homepage_blocks', ['theme_name' => 'garage', 'title' => 'Block từ ZIP cũ']);
    }

    public function test_query_param_sets_preview_theme_for_admin(): void
    {
        $manager = app(ThemeManager::class);
        $manager->install($this->makeZip(['name' => 'preview-theme']));

        $request = Request::create('/?theme=preview-theme', 'GET');
        $request->setLaravelSession($this->app['session.store']);
        $request->session()->put('admin_id', 1);

        app(ThemeBoot::class)->handle($request, fn ($r) => response('ok'));

        $this->assertSame('preview-theme', $request->session()->get('preview_theme'));
        $this->assertSame('preview-theme', app('theme.name'));
    }

    public function test_query_param_ignored_for_non_admin(): void
    {
        $manager = app(ThemeManager::class);
        $manager->install($this->makeZip(['name' => 'preview-theme']));

        $request = Request::create('/?theme=preview-theme', 'GET');
        $request->setLaravelSession($this->app['session.store']);

        app(ThemeBoot::class)->handle($request, fn ($r) => response('ok'));

        $this->assertNull($request->session()->get('preview_theme'));
    }

    public function test_delete_active_theme_is_rejected(): void
    {
        $manager = app(ThemeManager::class);
        $manager->install($this->makeZip(['name' => 'sample']));
        $manager->activate('sample');

        $this->expectException(ThemeInstallException::class);
        $manager->delete('sample');
    }

    public function test_delete_theme_removes_files_assets_layout_blocks_package_and_preview_session(): void
    {
        $manager = app(ThemeManager::class);
        $manager->install($this->makeZip(['name' => 'sample']));
        HomepageBlock::create([
            'theme_name' => 'sample',
            'zone' => 'main',
            'block_type' => 'editor',
            'style_key' => 'editor',
            'title' => 'Sample block',
            'settings' => ['content' => 'Demo'],
            'sort_order' => 1,
            'is_active' => true,
        ]);
        File::put($this->themePackagesPath.'/sample.zip', 'zip-content');
        session()->put('preview_theme', 'sample');

        $manager->delete('sample');

        $this->assertDirectoryDoesNotExist($this->themesPath.'/sample');
        $this->assertDirectoryDoesNotExist($this->publicAssetsPath.'/sample');
        $this->assertFileDoesNotExist($this->themePackagesPath.'/sample.zip');
        $this->assertSame(0, HomepageBlock::where('theme_name', 'sample')->count());
        $this->assertNull(session()->get('preview_theme'));
    }

    public function test_missing_theme_folder_falls_back_to_default(): void
    {
        Setting::set(ThemeManager::SETTING_KEY, 'ghost-theme');

        ThemeServiceProvider::bindActiveTheme('ghost-theme');

        $this->assertSame('default', app('theme.name'));
    }
}
