<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\Platform;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PublicStorageController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TestimonialController;
use Illuminate\Support\Facades\Route;

Route::prefix((string) config('tenancy.platform_path', 'platform'))
    ->name('platform.')
    ->middleware('platform.only')
    ->group(function () {
        Route::get('/login', [Platform\AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [Platform\AuthController::class, 'login'])
            ->middleware('throttle:5,1')
            ->name('login.post');

        Route::middleware('platform.auth')->group(function () {
            Route::post('/logout', [Platform\AuthController::class, 'logout'])->name('logout');
            Route::get('/', fn () => redirect()->route('platform.tenants.index'))->name('dashboard');
            Route::get('/catalog', [Platform\CatalogController::class, 'index'])->name('catalog.index');
            Route::post('/catalog/themes', [Platform\CatalogController::class, 'uploadTheme'])->name('catalog.themes.upload');
            Route::post('/catalog/plugins', [Platform\CatalogController::class, 'uploadPlugin'])->name('catalog.plugins.upload');
            Route::delete('/catalog/themes/{name}', [Platform\CatalogController::class, 'deleteTheme'])
                ->where('name', '[a-z0-9][a-z0-9_-]*')
                ->name('catalog.themes.delete');
            Route::delete('/catalog/plugins/{name}', [Platform\CatalogController::class, 'deletePlugin'])
                ->where('name', '[a-z0-9][a-z0-9_-]*')
                ->name('catalog.plugins.delete');
            Route::get('/admins', [Platform\PlatformAdminController::class, 'index'])->name('admins.index');
            Route::post('/admins', [Platform\PlatformAdminController::class, 'store'])->name('admins.store');
            Route::post('/admins/{platformAdmin}/status', [Platform\PlatformAdminController::class, 'status'])->name('admins.status');
            Route::get('/tenants', [Platform\TenantController::class, 'index'])->name('tenants.index');
            Route::get('/tenants/create', [Platform\TenantController::class, 'create'])->name('tenants.create');
            Route::post('/tenants', [Platform\TenantController::class, 'store'])->name('tenants.store');
            Route::get('/tenants/{tenant}', [Platform\TenantController::class, 'show'])->name('tenants.show');
            Route::get('/tenants/{tenant}/edit', [Platform\TenantController::class, 'edit'])->name('tenants.edit');
            Route::put('/tenants/{tenant}', [Platform\TenantController::class, 'update'])->name('tenants.update');
            Route::post('/tenants/{tenant}/status', [Platform\TenantController::class, 'status'])->name('tenants.status');
            Route::post('/tenants/{tenant}/health', [Platform\TenantController::class, 'health'])->name('tenants.health');
            Route::post('/tenants/{tenant}/migrate', [Platform\TenantController::class, 'migrate'])->name('tenants.migrate');
            Route::post('/tenants/{tenant}/package', [Platform\TenantController::class, 'package'])->name('tenants.package');
            Route::post('/tenants/{tenant}/seed', [Platform\TenantOperationController::class, 'seed'])->name('tenants.seed');
            Route::post('/tenants/{tenant}/cache', [Platform\TenantOperationController::class, 'clearCache'])->name('tenants.cache');
            Route::post('/tenants/{tenant}/admins', [Platform\TenantOperationController::class, 'saveAdministrator'])->name('tenants.admins.store');
            Route::put('/tenants/{tenant}/admins/{adminId}', [Platform\TenantOperationController::class, 'updateAdministrator'])->name('tenants.admins.update');
            Route::post('/tenants/{tenant}/admins/{adminId}/status', [Platform\TenantOperationController::class, 'administratorStatus'])->name('tenants.admins.status');
        });
    });

// ── Frontend ──────────────────────────────────────────────
// Fallback for deployments where public/storage cannot be symlinked.
// With a healthy link, Nginx/Apache serves these files without invoking PHP.
Route::get('/storage/{path}', [PublicStorageController::class, 'show'])
    ->where('path', '.*')
    ->name('public-storage.show');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/gioi-thieu', fn () => view('about'))->name('about');
Route::get('/tu-van', fn () => view()->exists('consulting') ? view('consulting') : abort(404))->name('consulting');
Route::get('/tuyen-sinh', fn () => view()->exists('admissions') ? view('admissions') : abort(404))->name('admissions');
Route::get('/diem-noi-bat', fn () => view('highlights'))->name('highlights');
Route::get('/khoa-hoc-noi-bat', fn () => view('courses'))->name('featured-courses');
Route::get('/san-pham', [ProductController::class, 'index'])->name('products.index');
Route::get('/san-pham/{slug}', [ProductController::class, 'show'])->name('product.show');
Route::get('/dich-vu', [ServiceController::class, 'index'])->name('services.index');
Route::get('/dich-vu/{slug}', [ServiceController::class, 'show'])->name('service.show');
Route::get('/tin-tuc', [NewsController::class, 'index'])->name('news.index');
Route::get('/tin-tuc/{slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('/lien-he', [ContactController::class, 'index'])->name('contact');
Route::post('/lien-he', [ContactController::class, 'store'])->name('contact.store');
Route::get('/danh-gia', [TestimonialController::class, 'index'])->name('testimonials.index');

// ── Admin ─────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/login', [Admin\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [Admin\AuthController::class, 'login'])->name('login.post');
    Route::get('/logout', [Admin\AuthController::class, 'logout'])->name('logout');

    Route::middleware('admin.auth')->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::get('/doi-mat-khau', [Admin\AuthController::class, 'showChangePassword'])->name('password.change');
        Route::post('/doi-mat-khau', [Admin\AuthController::class, 'changePassword'])->name('password.update');

        Route::get('/tai-khoan', [Admin\AuthController::class, 'showProfile'])->name('profile');
        Route::post('/tai-khoan', [Admin\AuthController::class, 'updateProfile'])->name('profile.update');

        Route::resource('products', Admin\ProductController::class)->except(['show']);
        Route::resource('services', Admin\ServiceController::class)->except(['show']);
        Route::resource('categories', Admin\CategoryController::class)->except(['show']);
        Route::resource('news', Admin\NewsController::class)->except(['show']);
        Route::resource('contacts', Admin\ContactController::class)->only(['index', 'show', 'destroy']);
        Route::post('/testimonials/import-google-reviews', [Admin\TestimonialController::class, 'importGoogleReviews'])->name('testimonials.import-google-reviews');
        Route::post('/testimonials/import-google-api', [Admin\TestimonialController::class, 'importGoogleApi'])->name('testimonials.import-google-api');
        Route::resource('testimonials', Admin\TestimonialController::class)->except(['show']);
        Route::resource('faqs', Admin\FaqController::class)->except(['show']);

        // Local Business (Structured Data)
        Route::post('/local-businesses/import-from-crawl', [Admin\LocalBusinessController::class, 'importFromCrawl'])->name('local-businesses.import-from-crawl');
        Route::post('/local-businesses/import-from-json-ld', [Admin\LocalBusinessController::class, 'importFromJsonLd'])->name('local-businesses.import-from-json-ld');
        Route::post('/local-businesses/{localBusiness}/sync-rating', [Admin\LocalBusinessController::class, 'syncRating'])->name('local-businesses.sync-rating');
        Route::get('/local-businesses/{localBusiness}/preview', [Admin\LocalBusinessController::class, 'preview'])->name('local-businesses.preview');
        Route::resource('local-businesses', Admin\LocalBusinessController::class)->except(['show']);

        // Legacy redirect
        Route::get('/settings', fn () => redirect()->route('admin.website-config.general'))->name('settings');

        // Cấu hình Website
        Route::get('/cau-hinh-website', fn () => redirect()->route('admin.website-config.general'))->name('website-config');
        Route::get('/cau-hinh-website/thong-tin-website', [Admin\SettingController::class, 'infoPage'])->name('website-config.info');
        Route::post('/cau-hinh-website/thong-tin-website', [Admin\SettingController::class, 'updateInfo'])->name('website-config.info.update');
        Route::get('/cau-hinh-website/cau-hinh-chung', [Admin\SettingController::class, 'generalPage'])->name('website-config.general');
        Route::post('/cau-hinh-website/cau-hinh-chung', [Admin\SettingController::class, 'updateGeneral'])->name('website-config.general.update');
        Route::get('/cau-hinh-website/cau-hinh-phan-trang', [Admin\SettingController::class, 'paginationPage'])->name('website-config.pagination');
        Route::post('/cau-hinh-website/cau-hinh-phan-trang', [Admin\SettingController::class, 'updatePagination'])->name('website-config.pagination.update');
        Route::get('/cau-hinh-website/bao-tri-website', [Admin\SettingController::class, 'maintenancePage'])->name('website-config.maintenance');
        Route::post('/cau-hinh-website/bao-tri-website', [Admin\SettingController::class, 'updateMaintenance'])->name('website-config.maintenance.update');
        Route::post('/cau-hinh-website/update-hotline', [Admin\SettingController::class, 'updateHotline'])->name('website-config.hotline.update');

        Route::get('/themes', [Admin\ThemeController::class, 'index'])->name('themes.index');
        Route::post('/themes/upload', [Admin\ThemeController::class, 'upload'])->name('themes.upload');
        Route::get('/themes/{name}/activate', [Admin\ThemeController::class, 'activate'])->name('themes.activate.get');
        Route::post('/themes/{name}/activate', [Admin\ThemeController::class, 'activate'])->name('themes.activate');
        Route::delete('/themes/{name}', [Admin\ThemeController::class, 'destroy'])->name('themes.destroy');

        Route::get('/plugins', [Admin\PluginController::class, 'index'])->name('plugins.index');
        Route::post('/plugins/upload', [Admin\PluginController::class, 'upload'])->name('plugins.upload');
        Route::post('/plugins/{name}/activate', [Admin\PluginController::class, 'activate'])->name('plugins.activate');
        Route::post('/plugins/{name}/deactivate', [Admin\PluginController::class, 'deactivate'])->name('plugins.deactivate');
        Route::get('/plugins/{name}/settings', [Admin\PluginController::class, 'settings'])->name('plugins.settings');
        Route::post('/plugins/{name}/settings', [Admin\PluginController::class, 'updateSettings'])->name('plugins.settings.update');
        Route::delete('/plugins/{name}', [Admin\PluginController::class, 'destroy'])->name('plugins.destroy');

        Route::get('/bo-cuc-trang-chu', [Admin\HomepageLayoutController::class, 'index'])->name('homepage-layout.index');
        Route::post('/bo-cuc-trang-chu', [Admin\HomepageLayoutController::class, 'store'])->name('homepage-layout.store');
        Route::put('/bo-cuc-trang-chu/{homepageBlock}', [Admin\HomepageLayoutController::class, 'update'])->name('homepage-layout.update');
        Route::delete('/bo-cuc-trang-chu/{homepageBlock}', [Admin\HomepageLayoutController::class, 'destroy'])->name('homepage-layout.destroy');
        Route::post('/bo-cuc-trang-chu/reorder', [Admin\HomepageLayoutController::class, 'reorder'])->name('homepage-layout.reorder');
        Route::post('/bo-cuc-trang-chu/widget-store', [Admin\HomepageLayoutController::class, 'widgetStore'])->name('homepage-layout.widget-store');

        // Image Crawler
        Route::get('/crawl-images', [Admin\ImageCrawlerController::class, 'index'])->name('image-crawler.index');
        Route::post('/crawl-images', [Admin\ImageCrawlerController::class, 'crawl'])->name('image-crawler.crawl');
        Route::get('/bo-cuc-trang-chu/widget-edit', [Admin\HomepageLayoutController::class, 'widgetEdit'])->name('homepage-layout.widget-edit');
        Route::post('/bo-cuc-trang-chu/widget-update', [Admin\HomepageLayoutController::class, 'widgetUpdate'])->name('homepage-layout.widget-update');

        Route::post('/menu-items/reorder', [Admin\MenuItemController::class, 'reorder'])->name('menu-items.reorder');
        Route::post('/menu-items/{menuItem}/toggle', [Admin\MenuItemController::class, 'toggle'])->name('menu-items.toggle');
        Route::resource('menu-items', Admin\MenuItemController::class)->except(['show']);

        Route::resource('html-tags', Admin\HtmlTagController::class)
            ->except(['show'])
            ->parameters(['html-tags' => 'htmlTag']);
        Route::post('/html-tags/{htmlTag}/toggle', [Admin\HtmlTagController::class, 'toggle'])->name('html-tags.toggle');

        Route::resource('admin-roles', Admin\AdminRoleController::class)->except(['show']);
        Route::resource('admin-users', Admin\AdminUserController::class)->except(['show']);
    });
});
