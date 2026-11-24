<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $cats = [
            ['name' => 'Massage',        'slug' => 'massage'],
            ['name' => 'Chăm Sóc Da',   'slug' => 'cham-soc-da'],
            ['name' => 'Chăm Sóc Body', 'slug' => 'cham-soc-body'],
            ['name' => 'Xông Hơi',      'slug' => 'xong-hoi'],
            ['name' => 'Chăm Sóc Tóc',  'slug' => 'cham-soc-toc'],
        ];

        $categoryIds = [];
        foreach ($cats as $i => $cat) {
            $c = Category::firstOrCreate(
                ['slug' => $cat['slug']],
                ['name' => $cat['name'], 'sort_order' => $i + 1, 'is_active' => true]
            );
            $categoryIds[$cat['slug']] = $c->id;
        }

        $services = [
            [
                'category' => 'massage',
                'name' => 'Massage Thư Giãn Toàn Thân',
                'slug' => 'massage-thu-gian-toan-than',
                'short_desc' => 'Liệu trình massage toàn thân giúp thư giãn cơ bắp, giảm căng thẳng và mệt mỏi sau ngày dài làm việc.',
                'description' => '<p>Liệu trình massage toàn thân với kỹ thuật Thụy Điển kết hợp tinh dầu thiên nhiên, tác động lên toàn bộ cơ thể từ đầu đến chân.</p><ul><li>Thư giãn cơ bắp sâu</li><li>Cải thiện tuần hoàn máu</li><li>Giảm stress hiệu quả</li><li>Tăng cường hệ miễn dịch</li></ul>',
                'price' => 350000,
                'duration' => '60 phút',
                'image' => 'theme-assets/lotus-spa/images/services/massage.jpg',
                'is_featured' => true,
            ],
            [
                'category' => 'cham-soc-da',
                'name' => 'Chăm Sóc Da Mặt Chuyên Sâu',
                'slug' => 'cham-soc-da-mat-chuyen-sau',
                'short_desc' => 'Làm sạch sâu, tẩy tế bào chết và dưỡng ẩm chuyên sâu, mang lại làn da tươi sáng và mịn màng.',
                'description' => '<p>Quy trình chăm sóc da chuyên sâu gồm 5 bước: làm sạch, tẩy tế bào chết, xông hơi, đắp mặt nạ và dưỡng ẩm.</p><ul><li>Làm sạch lỗ chân lông</li><li>Cấp ẩm chuyên sâu</li><li>Da sáng đều màu</li></ul>',
                'price' => 280000,
                'duration' => '60 phút',
                'image' => 'theme-assets/lotus-spa/images/services/facial.jpg',
                'is_featured' => true,
            ],
            [
                'category' => 'cham-soc-body',
                'name' => 'Chăm Sóc Body Toàn Thân',
                'slug' => 'cham-soc-body-toan-than',
                'short_desc' => 'Tẩy tế bào chết, dưỡng da toàn thân trắng sáng, mịn màng với các tinh chất thiên nhiên cao cấp.',
                'description' => '<p>Liệu trình chăm sóc body toàn diện giúp loại bỏ tế bào chết, cấp ẩm và làm sáng da toàn thân.</p><ul><li>Tẩy tế bào chết nhẹ nhàng</li><li>Ủ dưỡng trắng da</li><li>Massage thư giãn kết hợp</li></ul>',
                'price' => 480000,
                'duration' => '90 phút',
                'image' => 'theme-assets/lotus-spa/images/services/body.jpg',
                'is_featured' => true,
            ],
            [
                'category' => 'xong-hoi',
                'name' => 'Xông Hơi Thải Độc Thảo Dược',
                'slug' => 'xong-hoi-thai-doc-thao-duoc',
                'short_desc' => 'Xông hơi với thảo dược thiên nhiên giúp thải độc tố qua da, làm sạch lỗ chân lông và cải thiện sức khỏe tổng thể.',
                'description' => '<p>Kết hợp xông hơi với các loại thảo dược quý như gừng, sả, bạc hà giúp thư giãn và tăng cường sức đề kháng.</p><ul><li>Thải độc tố qua da</li><li>Làm sạch lỗ chân lông</li><li>Cải thiện hô hấp</li></ul>',
                'price' => 200000,
                'duration' => '30 phút',
                'image' => 'theme-assets/lotus-spa/images/services/sauna.jpg',
                'is_featured' => true,
            ],
            [
                'category' => 'cham-soc-toc',
                'name' => 'Chăm Sóc Tóc Phục Hồi',
                'slug' => 'cham-soc-toc-phuc-hoi',
                'short_desc' => 'Nuôi dưỡng tóc chắc khỏe, suôn mượt với keratin và protein tự nhiên, giảm gãy rụng và phục hồi tóc hư tổn.',
                'description' => '<p>Liệu trình chăm sóc tóc chuyên sâu với keratin và protein tự nhiên, phục hồi tóc hư tổn, khô xơ và chẻ ngọn.</p><ul><li>Phục hồi tóc hư tổn</li><li>Giảm gãy rụng</li><li>Tóc mềm mượt bóng khỏe</li></ul>',
                'price' => 300000,
                'duration' => '45 phút',
                'image' => 'theme-assets/lotus-spa/images/services/hair.jpg',
                'is_featured' => true,
            ],
        ];

        foreach ($services as $data) {
            $catSlug = $data['category'];
            unset($data['category']);
            $data['category_id'] = $categoryIds[$catSlug];
            $data['is_active'] = true;
            Service::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
