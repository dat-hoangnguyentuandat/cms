<?php

namespace Database\Seeders;

use App\Models\HomepageBlock;
use Illuminate\Database\Seeder;

class PhonixSidebarMenuSeeder extends Seeder
{
    public function run(): void
    {
        HomepageBlock::updateOrCreate(
            [
                'theme_name' => 'testdauvao',
                'zone' => 'float_left',
                'block_type' => 'menu',
                'style_key' => 'menu_1',
            ],
            [
                'title' => 'Menu Sidebar',
                'settings' => [
                    'items' => [
                        ['label' => 'Trang chủ', 'url' => '/', 'icon' => 'home', 'group' => 'main', 'active' => true],
                        ['label' => 'Giới thiệu', 'url' => '/gioi-thieu', 'icon' => 'info', 'group' => 'main'],
                        ['label' => 'Điểm nổi bật', 'url' => '/diem-noi-bat', 'icon' => 'star', 'group' => 'main'],
                        ['label' => 'Khóa học nổi bật', 'url' => '/khoa-hoc-noi-bat', 'icon' => 'menu_book', 'group' => 'main'],
                        ['label' => 'Test đầu vào', 'url' => '/test-dau-vao', 'icon' => 'edit_square', 'group' => 'main', 'highlight' => true],
                        ['label' => 'Kho từ vựng', 'url' => '#', 'icon' => 'library_books', 'group' => 'utility'],
                        ['label' => 'Luyện nghe', 'url' => '#', 'icon' => 'headphones', 'group' => 'utility'],
                        ['label' => 'Thư viện', 'url' => '#', 'icon' => 'local_library', 'group' => 'utility'],
                        ['label' => 'Diễn đàn', 'url' => '#', 'icon' => 'forum', 'group' => 'utility'],
                    ],
                ],
                'sort_order' => 1,
                'is_active' => true,
            ]
        );
    }
}
