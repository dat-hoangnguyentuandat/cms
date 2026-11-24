<?php

namespace App\Models;

use App\Services\Admin\PermissionRegistry;
use Illuminate\Database\Eloquent\Model;

class AdminRole extends Model
{
    protected $fillable = ['name', 'permissions', 'is_active'];

    protected $casts = ['permissions' => 'array', 'is_active' => 'boolean'];

    public const PERMISSIONS = [
        'dashboard' => 'Trang chủ',
        'products' => 'Quản lý sản phẩm',
        'services' => 'Quản lý dịch vụ',
        'categories' => 'Quản lý danh mục',
        'news' => 'Quản lý tin tức',
        'testimonials' => 'Đánh giá',
        'faqs' => 'FAQ',
        'contacts' => 'Liên hệ',
        'menu-items' => 'Quản lý menu',
        'themes' => 'Theme',
        'homepage-layout' => 'Bố cục trang chủ',
        'html-tags' => 'HTML Tags',
        'local-businesses' => 'LocalBusiness SEO',
        'settings' => 'Cài đặt',
        'plugins' => 'Plugins',
        'admin-users' => 'Quản lý Admin',
    ];

    public static function permissions(): array
    {
        return app()->bound(PermissionRegistry::class)
            ? app(PermissionRegistry::class)->all()
            : self::PERMISSIONS;
    }

    public function adminUsers()
    {
        return $this->hasMany(AdminUser::class, 'role_id');
    }

    public function hasPermission(string $key): bool
    {
        return in_array($key, $this->permissions ?? []);
    }
}
