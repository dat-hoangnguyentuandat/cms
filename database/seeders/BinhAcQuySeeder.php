<?php

namespace Database\Seeders;

use App\Models\HomepageBlock;
use App\Services\Theme\ThemeSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class BinhAcQuySeeder extends Seeder
{
    public function run(): void
    {
        $themePath = base_path('themes/binhacquy');
        $seedFile = $themePath.'/seed.php';

        if (! File::exists($seedFile)) {
            return;
        }

        $seed = require $seedFile;

        $this->deleteRowsExcept('products', 'slug', array_column($seed['products'] ?? [], 'slug'));
        $this->deleteRowsExcept('services', 'slug', array_column($seed['services'] ?? [], 'slug'));
        $this->deleteRowsExcept('news', 'slug', array_column($seed['news'] ?? [], 'slug'));
        $this->deleteLegacySeedTestimonials();
        $this->deleteRowsExcept('categories', 'slug', array_column($seed['categories'] ?? [], 'slug'));
        if (Schema::hasTable('menu_items')) {
            DB::table('menu_items')->delete();
        }

        app(ThemeSeeder::class)->run($themePath);

        $this->syncHomepageBlocks();
    }

    private function deleteLegacySeedTestimonials(): void
    {
        if (! Schema::hasTable('testimonials')) {
            return;
        }

        $legacyNames = ['Anh Minh', 'Chị Lan', 'Anh Tuấn', 'Chú Hòa'];

        DB::table('testimonials')
            ->whereIn('name', $legacyNames)
            ->where(function ($query) {
                $query->whereNull('source')->orWhere('source', '!=', 'google');
            })
            ->delete();
    }

    private function deleteRowsExcept(string $table, string $column, array $allowed): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        DB::table($table)
            ->when(! empty($allowed), fn ($query) => $query->whereNotIn($column, $allowed))
            ->delete();
    }

    private function syncHomepageBlocks(): void
    {
        if (! Schema::hasTable('homepage_blocks')) {
            return;
        }

        $defaults = require base_path('themes/binhacquy/widgets.php');

        foreach ($defaults['defaults'] ?? [] as $index => $block) {
            $attributes = [
                'theme_name' => 'binhacquy',
                'zone' => $block['zone'],
                'block_type' => $block['block_type'],
                'style_key' => $block['style_key'],
            ];

            HomepageBlock::updateOrCreate($attributes, [
                'title' => $block['title'] ?? '',
                'settings' => $block['settings'] ?? [],
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }

        $mainOrder = [
            'banner' => 1,
            'service-section' => 2,
            'product-group' => 3,
            'stats' => 4,
            'post-group' => 5,
            'customer-testimonials' => 6,
            'newsletter' => 7,
        ];

        foreach ($mainOrder as $type => $order) {
            HomepageBlock::where('theme_name', 'binhacquy')
                ->where('zone', 'main')
                ->where('block_type', $type)
                ->update(['sort_order' => $order]);
        }
    }

}
