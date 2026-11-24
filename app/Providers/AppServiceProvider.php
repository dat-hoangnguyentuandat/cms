<?php

namespace App\Providers;

use App\Models\AdminRole;
use App\Services\Admin\AdminMenuRegistry;
use App\Services\Admin\PermissionRegistry;
use App\Services\Admin\RoutePermissionRegistry;
use App\Services\Plugin\CoreShortcodeRegistrar;
use App\Services\Plugin\HookManager;
use App\Services\Plugin\PluginManager;
use App\Services\Plugin\ShortcodeManager;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantDatabaseManager;
use App\Tenancy\TenantPackageAccess;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AdminMenuRegistry::class);
        $this->app->singleton(PermissionRegistry::class);
        $this->app->singleton(RoutePermissionRegistry::class);
        $this->app->singleton(HookManager::class);
        $this->app->singleton(ShortcodeManager::class);
        $this->app->singleton(CoreShortcodeRegistrar::class);
        $this->app->singleton(PluginManager::class, fn ($app) => new PluginManager(
            base_path('plugins'),
            $app->make(TenantPackageAccess::class),
        ));
        $this->app->singleton(TenantContext::class);
        $this->app->singleton(TenantDatabaseManager::class);
        $this->app->singleton(TenantPackageAccess::class);
    }

    public function boot(): void
    {
        Blade::directive('htmlTags', function (string $expression) {
            return "<?php echo \\App\\Models\\HtmlTag::renderPosition({$expression}); ?>";
        });

        $this->registerCoreAdminExtensionPoints();
        app(CoreShortcodeRegistrar::class)->register(app(ShortcodeManager::class));
        app(PluginManager::class)->discoverInstalled();
    }

    private function registerCoreAdminExtensionPoints(): void
    {
        app(PermissionRegistry::class)->registerMany(AdminRole::PERMISSIONS);

        $menu = app(AdminMenuRegistry::class);
        $menu->register('Tổng quan', ['route' => 'admin.dashboard', 'icon' => 'dashboard', 'label' => 'Trang chủ']);
        $menu->register('Cửa hàng', ['route' => 'admin.products.index', 'icon' => 'box', 'label' => 'Sản phẩm', 'permission' => 'products']);
        $menu->register('Cửa hàng', ['route' => 'admin.services.index', 'icon' => 'scissors', 'label' => 'Dịch vụ', 'permission' => 'services']);
        $menu->register('Cửa hàng', ['route' => 'admin.categories.index', 'icon' => 'tag', 'label' => 'Danh mục', 'permission' => 'categories']);
        $menu->register('Nội dung', ['route' => 'admin.news.index', 'icon' => 'news', 'label' => 'Tin tức', 'permission' => 'news']);
        $menu->register('Nội dung', ['route' => 'admin.testimonials.index', 'icon' => 'star', 'label' => 'Đánh giá', 'permission' => 'testimonials']);
        $menu->register('Nội dung', ['route' => 'admin.faqs.index', 'icon' => 'help', 'label' => 'FAQ', 'permission' => 'faqs']);
        $menu->register('Hệ thống', ['route' => 'admin.contacts.index', 'icon' => 'inbox', 'label' => 'Liên hệ', 'permission' => 'contacts']);
        $menu->register('Hệ thống', ['route' => 'admin.menu-items.index', 'icon' => 'menu', 'label' => 'Menu', 'permission' => 'menu-items']);
        $menu->register('Hệ thống', ['route' => 'admin.themes.index', 'icon' => 'palette', 'label' => 'Theme', 'permission' => 'themes']);
        $menu->register('Hệ thống', ['route' => 'admin.homepage-layout.index', 'icon' => 'layout', 'label' => 'Bố cục trang chủ', 'permission' => 'homepage-layout']);
        $menu->register('Hệ thống', ['route' => 'admin.html-tags.index', 'icon' => 'code', 'label' => 'HTML Tags', 'permission' => 'html-tags']);
        $menu->register('Hệ thống', ['route' => 'admin.local-businesses.index', 'icon' => 'building', 'label' => 'LocalBusiness SEO', 'permission' => 'local-businesses']);
        $menu->register('Hệ thống', ['route' => 'admin.website-config', 'icon' => 'settings', 'label' => 'Cấu hình Website', 'permission' => 'settings']);
        $menu->register('Hệ thống', ['route' => 'admin.plugins.index', 'icon' => 'plug', 'label' => 'Plugins', 'permission' => 'plugins']);
        $menu->register('Quản lý Admin', ['route' => 'admin.admin-users.index', 'icon' => 'user', 'label' => 'Quản lý Admin', 'permission' => 'admin-users']);
        $menu->register('Tài khoản', ['route' => 'admin.profile', 'icon' => 'user', 'label' => 'Thông tin tài khoản']);
        $menu->register('Tài khoản', ['route' => 'admin.password.change', 'icon' => 'key', 'label' => 'Đổi mật khẩu']);

        app(RoutePermissionRegistry::class)->registerMany([
            'admin.dashboard' => 'dashboard',
            'admin.products' => 'products',
            'admin.services' => 'services',
            'admin.categories' => 'categories',
            'admin.news' => 'news',
            'admin.testimonials' => 'testimonials',
            'admin.faqs' => 'faqs',
            'admin.contacts' => 'contacts',
            'admin.menu-items' => 'menu-items',
            'admin.themes' => 'themes',
            'admin.homepage-layout' => 'homepage-layout',
            'admin.html-tags' => 'html-tags',
            'admin.local-businesses' => 'local-businesses',
            'admin.website-config' => 'settings',
            'admin.settings' => 'settings',
            'admin.plugins' => 'plugins',
            'admin.admin-roles' => 'admin-users',
            'admin.admin-users' => 'admin-users',
        ]);
    }
}
