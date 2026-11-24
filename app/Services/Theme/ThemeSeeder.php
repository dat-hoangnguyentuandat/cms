<?php

namespace App\Services\Theme;

use App\Models\Category;
use App\Models\Faq;
use App\Models\LocalBusiness;
use App\Models\MenuItem;
use App\Models\News;
use App\Models\Product;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * Runs a theme's seed.php when the theme is activated.
 * seed.php returns an array with keys: settings, categories,
 * products, services, news, testimonials.
 *
 * The seed is authoritative: theme-owned content is cleared and rebuilt so
 * no catalog, review, menu or brand setting can bleed across themes.
 */
class ThemeSeeder
{
    /** Settings that describe the active site's brand/content, not the CMS itself. */
    private const THEME_SETTING_KEYS = [
        'about_desc', 'about_title', 'address', 'email', 'facebook_url',
        'google_map_embed_url', 'google_maps_url', 'google_review_url',
        'hero_desc', 'hero_heading', 'hero_lead', 'hotline', 'instagram_url',
        'news_per_page', 'news_related_count', 'products_per_page',
        'services_per_page', 'services_related_count', 'site_desc', 'site_logo',
        'site_name', 'site_tagline', 'testimonials_per_page', 'working_hours',
        'youtube_url', 'zalo',
    ];

    public function run(string $themePath): void
    {
        $file = $themePath.DIRECTORY_SEPARATOR.'seed.php';

        if (! File::exists($file)) {
            return;
        }

        $data = require $file;

        if (! is_array($data)) {
            return;
        }

        try {
            DB::transaction(function () use ($data) {
                $this->clearThemeContent();
                $this->seedSettings($data['settings'] ?? []);
                $catIds = $this->seedCategories($data['categories'] ?? []);
                $this->seedProducts($data['products'] ?? [], $catIds);
                $this->seedServices($data['services'] ?? [], $catIds);
                $this->seedNews($data['news'] ?? [], $catIds);
                $this->seedTestimonials($data['testimonials'] ?? []);
                $this->seedMenuItems($data['menu_items'] ?? []);
            });
        } finally {
            Setting::clearCache();
        }
    }

    /** Remove all content owned by the previously active theme. */
    private function clearThemeContent(): void
    {
        foreach ([
            'products' => Product::class,
            'services' => Service::class,
            'news' => News::class,
            'testimonials' => Testimonial::class,
            'menu_items' => MenuItem::class,
            'faqs' => Faq::class,
            'local_businesses' => LocalBusiness::class,
        ] as $table => $model) {
            if (Schema::hasTable($table)) {
                $model::query()->delete();
            }
        }

        if (Schema::hasTable('categories')) {
            Category::query()->delete();
        }
    }

    private function seedSettings(array $settings): void
    {
        DB::table('settings')
            ->where(function ($query) {
                $query->where('setting_group', 'theme')
                    ->orWhereIn('setting_key', self::THEME_SETTING_KEYS);
            })
            ->whereNotIn('setting_key', array_keys($settings))
            ->delete();

        foreach ($settings as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['setting_key' => $key],
                ['setting_key' => $key, 'setting_value' => $value, 'setting_group' => 'theme']
            );
        }
    }

    private function seedCategories(array $categories): array
    {
        $catIds = [];

        foreach ($categories as $i => $cat) {
            $record = Category::updateOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name' => $cat['name'],
                    'sort_order' => $cat['sort_order'] ?? ($i + 1),
                    'is_active' => $cat['is_active'] ?? true,
                ]
            );
            $catIds[$cat['slug']] = $record->id;
        }

        return $catIds;
    }

    /**
     * @return array<int, string> slugs of the products owned by this theme
     */
    private function seedProducts(array $products, array $catIds): array
    {
        if (! Schema::hasTable('products')) {
            return [];
        }

        $slugs = [];

        foreach ($products as $data) {
            $catSlug = $data['category_slug'] ?? null;
            unset($data['category_slug']);

            if ($catSlug && isset($catIds[$catSlug])) {
                $data['category_id'] = $catIds[$catSlug];
            }

            Product::updateOrCreate(['slug' => $data['slug']], $data);
            $slugs[] = $data['slug'];
        }

        return $slugs;
    }

    /**
     * @return array<int, string> slugs of the services owned by this theme
     */
    private function seedServices(array $services, array $catIds): array
    {
        if (! Schema::hasTable('services')) {
            return [];
        }

        $slugs = [];

        foreach ($services as $data) {
            $catSlug = $data['category_slug'] ?? null;
            unset($data['category_slug']);

            if ($catSlug && isset($catIds[$catSlug])) {
                $data['category_id'] = $catIds[$catSlug];
            }

            Service::updateOrCreate(['slug' => $data['slug']], $data);
            $slugs[] = $data['slug'];
        }

        return $slugs;
    }

    /**
     * @return array<int, string> slugs of the news posts owned by this theme
     */
    private function seedNews(array $posts, array $catIds = []): array
    {
        if (! Schema::hasTable('news')) {
            return [];
        }

        $slugs = [];

        foreach ($posts as $data) {
            $catSlug = $data['category_slug'] ?? null;
            unset($data['category_slug']);

            if ($catSlug && isset($catIds[$catSlug])) {
                $data['category_id'] = $catIds[$catSlug];
            }

            News::updateOrCreate(['slug' => $data['slug']], $data);
            $slugs[] = $data['slug'];
        }

        return $slugs;
    }

    /**
     * @return array<int, string> names of the testimonials owned by this theme
     */
    private function seedTestimonials(array $reviews): array
    {
        if (! Schema::hasTable('testimonials')) {
            return [];
        }

        $names = [];

        foreach ($reviews as $i => $review) {
            Testimonial::updateOrCreate(
                ['name' => $review['name']],
                array_merge(['sort_order' => $i + 1, 'is_active' => true], $review)
            );
            $names[] = $review['name'];
        }

        return $names;
    }

    /**
     * Replace navigation with the menu declared by the activated theme.
     * Each item may carry a children array.
     */
    private function seedMenuItems(array $items): void
    {
        if (! Schema::hasTable('menu_items')) {
            return;
        }

        $allowed = ['parent_id', 'label', 'keyword', 'title', 'description',
            'type', 'url', 'target', 'sort_order', 'is_active',
            'seo_title', 'seo_description', 'image', 'section_name', 'slug'];
        $allowedTypes = [
            'custom', 'page', 'news_list', 'product_list', 'service_list',
            'news_cat', 'product_cat', 'service_cat',
        ];

        foreach (array_values($items) as $i => $item) {
            $children = $item['children'] ?? [];
            unset($item['children'], $item['route_name']);

            $attrs = array_intersect_key($item, array_flip($allowed));
            $attrs['sort_order'] = $attrs['sort_order'] ?? ($i + 1);
            $attrs['is_active'] = $attrs['is_active'] ?? true;
            $attrs['type'] = $attrs['type'] ?? 'custom';
            if (! in_array($attrs['type'], $allowedTypes, true)) {
                $attrs['type'] = 'custom';
            }

            $parent = MenuItem::create($attrs);

            foreach (array_values($children) as $j => $child) {
                unset($child['children'], $child['route_name']);
                $childAttrs = array_intersect_key($child, array_flip($allowed));
                $childAttrs['parent_id'] = $parent->id;
                $childAttrs['sort_order'] = $childAttrs['sort_order'] ?? ($j + 1);
                $childAttrs['is_active'] = $childAttrs['is_active'] ?? true;
                $childAttrs['type'] = $childAttrs['type'] ?? 'custom';
                if (! in_array($childAttrs['type'], $allowedTypes, true)) {
                    $childAttrs['type'] = 'custom';
                }
                MenuItem::create($childAttrs);
            }
        }
    }
}
