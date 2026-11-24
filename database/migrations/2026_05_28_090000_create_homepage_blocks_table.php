<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('zone', 50);
            $table->string('block_type', 80);
            $table->string('style_key', 80)->nullable();
            $table->string('title')->nullable();
            $table->json('settings')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['zone', 'sort_order']);
        });

        $now = now();
        $blocks = [
            ['header', 'header', 'classic', 'Header : Classic', 1],
            ['header', 'topbar', 'style-1', 'Topbar (kiểu 1) : Topbar Kiểu 1', 2],
            ['main', 'slider', null, 'Slider', 1],
            ['main', 'service-section', 'style-6', 'Khối dịch vụ (kiểu 6)', 2],
            ['main', 'product-list', 'style-2', 'Danh sách sản phẩm theo Thể loại (kiểu 2) : SẢN PHẨM MỚI NHẤT', 3],
            ['main', 'banner', 'style-2', 'Khối banner (kiểu 2)', 4],
            ['main', 'product-group', 'style-1', 'Nhóm sản phẩm (kiểu 1) : SẢN PHẨM BÁN CHẠY', 5],
            ['main', 'banner', 'style-1', 'Khối banner (kiểu 1)', 6],
            ['main', 'post-group', 'style-4', 'Nhóm bài viết (kiểu 4) : TIN TỨC MỚI NHẤT', 7],
            ['main', 'popup', 'style-1', 'Popup (kiểu 1) : Popup kiểu 1', 8],
            ['sidebar', 'fanpage', 'style-1', 'Fanpage FB (kiểu 1) : Fanpage', 1],
            ['sidebar', 'product-filter', 'style-1', 'Bộ lọc sản phẩm (kiểu 1) : Bộ lọc sản phẩm', 2],
            ['sidebar', 'product-group', 'style-3', 'Nhóm sản phẩm (kiểu 3) : Sản phẩm mới', 3],
            ['sidebar', 'post-group', 'style-3', 'Nhóm bài viết (kiểu 3) : Có thể bạn quan tâm', 4],
            ['footer_top', 'empty', null, null, 1],
            ['footer_1', 'editor', 'style-1', 'Khung soạn thảo (kiểu 1) : LIÊN HỆ', 1],
            ['footer_1', 'social-links', 'style-1', 'Liên kết mạng xã hội (kiểu 1)', 2],
            ['footer_2', 'menu', 'style-1', 'Menu (kiểu 1) : CHÍNH SÁCH BÁN HÀNG', 1],
            ['footer_3', 'newsletter', 'style-2', 'Đăng ký nhận tin (kiểu 2) : ĐĂNG KÝ NHẬN TIN', 1],
            ['footer_bottom', 'footer', 'style-1', 'Footer (kiểu 1) : footer kiểu 1', 1],
        ];

        foreach ($blocks as [$zone, $type, $style, $title, $order]) {
            DB::table('homepage_blocks')->insert([
                'zone' => $zone,
                'block_type' => $type,
                'style_key' => $style,
                'title' => $title,
                'settings' => json_encode([]),
                'sort_order' => $order,
                'is_active' => $type !== 'empty',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_blocks');
    }
};
