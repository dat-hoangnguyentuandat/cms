<?php

namespace Database\Seeders;

use App\Models\HomepageBlock;
use App\Services\Theme\ThemeSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class AcQuyNgocDieuSeeder extends Seeder
{
    public function run(): void
    {
        $themePath = base_path('themes/acquyngocdieu');
        $seedFile = $themePath.'/seed.php';

        if (! File::exists($seedFile)) {
            return;
        }

        $seed = require $seedFile;

        $this->deleteRowsExcept('products', 'slug', array_column($seed['products'] ?? [], 'slug'));
        $this->deleteRowsExcept('services', 'slug', array_column($seed['services'] ?? [], 'slug'));
        $this->deleteRowsExcept('news', 'slug', array_column($seed['news'] ?? [], 'slug'));
        $this->deleteRowsExcept('categories', 'slug', array_column($seed['categories'] ?? [], 'slug'));
        if (Schema::hasTable('menu_items')) {
            DB::table('menu_items')->delete();
        }

        app(ThemeSeeder::class)->run($themePath);

        $this->syncHomepageBlocks();
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

        $defaults = require base_path('themes/acquyngocdieu/widgets.php');

        foreach ($defaults['defaults'] ?? [] as $index => $block) {
            $attributes = [
                'theme_name' => 'acquyngocdieu',
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
    }
}
