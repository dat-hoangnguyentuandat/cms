<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the sort_order of the promotions block
        $promotionsBlock = DB::table('homepage_blocks')
            ->where('theme_name', 'six-spa')
            ->where('zone', 'main')
            ->where('block_type', 'promotions')
            ->first();

        if ($promotionsBlock) {
            // Increment sort_order of all blocks after promotions
            DB::table('homepage_blocks')
                ->where('theme_name', 'six-spa')
                ->where('zone', 'main')
                ->where('sort_order', '>', $promotionsBlock->sort_order)
                ->increment('sort_order');

            // Insert news widget after promotions
            DB::table('homepage_blocks')->insert([
                'theme_name' => 'six-spa',
                'zone' => 'main',
                'block_type' => 'list-of-articles-by-category',
                'style_key' => 'list-of-articles-by-category_2',
                'title' => 'Tin tức mới nhất',
                'settings' => json_encode([
                    'display_mode' => 'show',
                    'category_ids' => [],
                    'limit_per_category' => 6,
                    'desktop_items' => 4,
                    'mobile_items' => 2,
                    'show_children' => false,
                    'show_all_tab' => true,
                    'autoplay' => false,
                    'title_align' => 'center',
                    'show_title' => true,
                    'show_categories' => true,
                    'show_category_images' => false,
                    'hide_parent_categories' => false,
                ]),
                'sort_order' => $promotionsBlock->sort_order + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('homepage_blocks')
            ->where('theme_name', 'six-spa')
            ->where('block_type', 'list-of-articles-by-category')
            ->delete();
    }
};
