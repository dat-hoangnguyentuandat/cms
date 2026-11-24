<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'parent_id', 'label', 'keyword', 'title', 'description',
        'type', 'url', 'target', 'sort_order', 'is_active',
        'seo_title', 'seo_description', 'image', 'section_name', 'slug',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function getResolvedUrlAttribute(): string
    {
        if ($this->url) {
            return $this->url;
        }

        if ($this->slug) {
            return match ($this->type) {
                'service_cat' => '/dich-vu?cat='.$this->slug,
                'news_cat' => '/tin-tuc?danh-muc='.$this->slug,
                'product_cat' => '/san-pham?cat='.$this->slug,
                default => '/'.ltrim($this->slug, '/'),
            };
        }

        return match ($this->type) {
            'service_list' => '/dich-vu',
            'service_cat' => '/dich-vu',
            'product_list' => '/san-pham',
            'product_cat' => '/san-pham',
            'news_list' => '/tin-tuc',
            'news_cat' => '/tin-tuc',
            'contact' => '/lien-he',
            'page' => '/gioi-thieu',
            default => '/',
        };
    }

    public function getImageUrlAttribute(): string
    {
        if (! $this->image) {
            return '';
        }
        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }
        if (str_starts_with($this->image, 'theme-assets/')) {
            return asset($this->image);
        }

        return asset('storage/'.$this->image);
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function isParent(): bool
    {
        return is_null($this->parent_id);
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'custom' => 'Trang nội dung',
            'page' => 'Trang giới thiệu',
            'news_list' => 'Trang tin tức',
            'product_list' => 'Trang sản phẩm',
            'service_list' => 'Trang dịch vụ',
            'news_cat' => 'Danh mục tin tức',
            'product_cat' => 'Danh mục sản phẩm',
            'service_cat' => 'Danh mục dịch vụ',
            'contact' => 'Trang liên hệ',
            default => $type,
        };
    }

    public static function typeBadgeClass(string $type): string
    {
        return match ($type) {
            'custom' => 'badge-secondary',
            'news_list', 'news_cat' => 'badge-info',
            'product_list', 'product_cat' => 'badge-success',
            'service_list', 'service_cat' => 'badge-warning',
            'page' => 'badge-primary',
            'contact' => 'badge-danger',
            default => 'badge-secondary',
        };
    }
}
