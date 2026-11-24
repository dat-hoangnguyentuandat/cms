<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\News;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SixSpaSeeder extends Seeder
{
    public function run(): void
    {
        $seedData = require base_path('themes/six-spa/seed.php');

        $this->clearThemeContent();
        $this->settings($seedData['settings']);
        $categoryIds = $this->categories($seedData['categories']);
        $this->services($seedData['services'], $categoryIds);
        $this->news($seedData['news']);
        $this->testimonials($seedData['testimonials']);
        $this->homepageBlocks();
    }

    private function clearThemeContent(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Service::query()->truncate();
        News::query()->truncate();
        Category::query()->truncate();
        Testimonial::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function settings(array $settings): void
    {
        // Set active theme
        Setting::set('active_theme', 'six-spa');

        // Set all theme settings
        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }
    }

    private function categories(array $categories): array
    {
        $ids = [];
        foreach ($categories as $data) {
            $category = Category::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, ['is_active' => true])
            );
            $ids[$data['slug']] = $category->id;
        }

        return $ids;
    }

    private function services(array $services, array $categoryIds): void
    {
        foreach ($services as $data) {
            $categorySlug = $data['category_slug'];
            unset($data['category_slug']);

            $data['category_id'] = $categoryIds[$categorySlug];

            Service::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }

    private function news(array $newsItems): void
    {
        foreach ($newsItems as $data) {
            News::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }

    private function testimonials(array $testimonials): void
    {
        foreach ($testimonials as $data) {
            Testimonial::create($data);
        }
    }

    private function homepageBlocks(): void
    {
        // Update slider widget with hero banner image
        DB::table('homepage_blocks')
            ->where('theme_name', 'six-spa')
            ->where('block_type', 'slider')
            ->update([
                'settings' => json_encode([
                    'slides' => [
                        [
                            'image' => 'theme-assets/six-spa/images/banner.jpg',
                            'heading' => 'SPA & BEAUTY',
                            'sub' => 'Chăm sóc sắc đẹp toàn diện',
                        ],
                    ],
                ]),
            ]);

        // Update stats widget with spa room background
        DB::table('homepage_blocks')
            ->where('theme_name', 'six-spa')
            ->where('block_type', 'stats')
            ->update([
                'settings' => json_encode([
                    'background_image' => 'theme-assets/six-spa/images/spa-room.jpg',
                    'stats' => [
                        ['num' => '10+', 'label' => 'Năm kinh nghiệm'],
                        ['num' => '5000+', 'label' => 'Khách hàng hài lòng'],
                        ['num' => '50+', 'label' => 'Chuyên viên tay nghề cao'],
                        ['num' => '100%', 'label' => 'Sản phẩm thiên nhiên'],
                    ],
                ]),
            ]);

        // Update product-list widget with promo images
        DB::table('homepage_blocks')
            ->where('theme_name', 'six-spa')
            ->where('block_type', 'product-list')
            ->update([
                'settings' => json_encode([
                    'promo_images' => [
                        'theme-assets/six-spa/images/promo/massage.jpg',
                        'theme-assets/six-spa/images/promo/facial.jpg',
                        'theme-assets/six-spa/images/promo/body.jpg',
                    ],
                ]),
            ]);

        // Update customer testimonials widget
        DB::table('homepage_blocks')
            ->where('theme_name', 'six-spa')
            ->where('block_type', 'customer-testimonials')
            ->update([
                'settings' => json_encode([
                    'content_source' => 'import',
                    'layout' => 'slider',
                    'desktop_cols' => 3,
                    'mobile_cols' => 1,
                    'autoplay' => true,
                    'autoplay_speed' => 5,
                    'show_role' => true,
                    'show_company' => true,
                    'star_position' => 'before_content',
                    'bg_color' => '#f8fafc',
                    'accent_color' => '#10b981',
                ]),
            ]);
    }
}
