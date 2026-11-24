<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class PhonixCourseProductSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::updateOrCreate(
            ['slug' => 'khoa-hoc-phonix'],
            [
                'name' => 'Khóa học Phonix',
                'description' => 'Các sản phẩm khóa học tiếng Anh đang mở bán tại Phonix.',
                'image' => null,
                'sort_order' => 1,
                'is_active' => true,
                'parent_id' => null,
            ]
        );

        $courses = [
            [
                'name' => 'Tiếng Anh Mầm Non 3-6 tuổi',
                'slug' => 'tieng-anh-mam-non-3-6-tuoi',
                'sku' => 'PHX-KID-36',
                'short_desc' => 'Khóa học làm quen tiếng Anh qua hình ảnh, bài hát, vận động và giao tiếp ngắn cho trẻ 3-6 tuổi.',
                'description' => '<p>Khóa học giúp trẻ làm quen âm thanh tiếng Anh, từ vựng gần gũi và phản xạ trả lời câu đơn giản thông qua hoạt động lớp học.</p><ul><li>Đối tượng: trẻ 3-6 tuổi.</li><li>Nội dung: phonics cơ bản, từ vựng chủ đề, bài hát, kể chuyện.</li><li>Kết quả: trẻ tự tin nghe hiểu và nói các mẫu câu quen thuộc.</li></ul>',
                'price' => 2800000,
                'sale_price' => 2490000,
                'stock' => 24,
                'image' => 'theme-assets/testdauvao/images/hero-student.png',
            ],
            [
                'name' => 'Tiếng Anh Tiểu Học 6-11 tuổi',
                'slug' => 'tieng-anh-tieu-hoc-6-11-tuoi',
                'sku' => 'PHX-PRI-611',
                'short_desc' => 'Lộ trình xây nền phát âm, từ vựng, ngữ pháp và giao tiếp cho học sinh tiểu học.',
                'description' => '<p>Chương trình tập trung vào giao tiếp nền tảng, đọc hiểu ngắn và thói quen học tiếng Anh đều đặn.</p><ul><li>Đối tượng: học sinh 6-11 tuổi.</li><li>Nội dung: phonics, từ vựng học đường, mẫu câu giao tiếp, đọc hiểu theo chủ đề.</li><li>Kết quả: học viên nói tự nhiên hơn và nắm chắc kiến thức nền.</li></ul>',
                'price' => 3200000,
                'sale_price' => 2890000,
                'stock' => 30,
                'image' => 'theme-assets/testdauvao/images/hero-student.png',
            ],
            [
                'name' => 'Tiếng Anh Thiếu Niên 11-16 tuổi',
                'slug' => 'tieng-anh-thieu-nien-11-16-tuoi',
                'sku' => 'PHX-TEEN-1116',
                'short_desc' => 'Khóa học tăng năng lực đọc, viết, thuyết trình và giao tiếp học thuật cho học sinh cấp 2-3.',
                'description' => '<p>Lộ trình giúp học viên thiếu niên củng cố ngữ pháp, mở rộng vốn từ và luyện trình bày ý tưởng bằng tiếng Anh.</p><ul><li>Đối tượng: học sinh 11-16 tuổi.</li><li>Nội dung: speaking projects, reading skills, writing paragraphs, grammar in use.</li><li>Kết quả: học viên dùng tiếng Anh tốt hơn trong học tập và hoạt động nhóm.</li></ul>',
                'price' => 3600000,
                'sale_price' => 3290000,
                'stock' => 28,
                'image' => 'theme-assets/testdauvao/images/hero-student.png',
            ],
            [
                'name' => 'Tiếng Anh Giao Tiếp Người Lớn',
                'slug' => 'tieng-anh-giao-tiep-nguoi-lon',
                'sku' => 'PHX-COM-ADULT',
                'short_desc' => 'Khóa học luyện phản xạ nghe nói, phát âm và tình huống giao tiếp thực tế cho người đi học, đi làm.',
                'description' => '<p>Khóa giao tiếp dành cho người lớn cần sử dụng tiếng Anh trong công việc, du lịch và đời sống hằng ngày.</p><ul><li>Đối tượng: học viên từ 17 tuổi.</li><li>Nội dung: pronunciation, listening response, role-play, workplace English.</li><li>Kết quả: học viên nói rõ ý hơn và phản xạ nhanh hơn trong tình huống thực tế.</li></ul>',
                'price' => 3400000,
                'sale_price' => 2990000,
                'stock' => 22,
                'image' => 'theme-assets/testdauvao/images/hero-assessment.jpg',
            ],
            [
                'name' => 'IELTS Foundation 4.0+',
                'slug' => 'ielts-foundation-4-0-plus',
                'sku' => 'PHX-IELTS-FDN',
                'short_desc' => 'Khóa nền IELTS cho học viên cần hệ thống lại 4 kỹ năng trước khi bước vào luyện đề.',
                'description' => '<p>Chương trình xây nền từ vựng học thuật, ngữ pháp cốt lõi và kỹ thuật làm bài IELTS ở mức nhập môn.</p><ul><li>Đối tượng: học viên mục tiêu IELTS 4.0+.</li><li>Nội dung: Listening, Reading, Writing Task 1/2 cơ bản, Speaking part 1-2.</li><li>Kết quả: học viên hiểu cấu trúc bài thi và có nền tảng để lên band cao hơn.</li></ul>',
                'price' => 4200000,
                'sale_price' => 3790000,
                'stock' => 18,
                'image' => 'theme-assets/testdauvao/images/hero-assessment.jpg',
            ],
            [
                'name' => 'Cambridge Young Learners',
                'slug' => 'cambridge-young-learners',
                'sku' => 'PHX-CAM-YLE',
                'short_desc' => 'Khóa luyện Starters, Movers, Flyers cho học viên nhỏ tuổi theo hướng vui học và bám sát kỹ năng.',
                'description' => '<p>Khóa học chuẩn bị cho các cấp độ Cambridge Young Learners, kết hợp luyện kỹ năng và hoạt động tương tác.</p><ul><li>Đối tượng: học viên tiểu học cần luyện Starters, Movers hoặc Flyers.</li><li>Nội dung: vocabulary topics, listening tasks, reading-writing, speaking practice.</li><li>Kết quả: học viên quen định dạng bài và tự tin sử dụng tiếng Anh hơn.</li></ul>',
                'price' => 3900000,
                'sale_price' => 3490000,
                'stock' => 20,
                'image' => 'theme-assets/testdauvao/images/hero-student.png',
            ],
        ];

        foreach ($courses as $course) {
            Product::updateOrCreate(
                ['slug' => $course['slug']],
                [
                    'category_id' => $category->id,
                    'name' => $course['name'],
                    'sku' => $course['sku'],
                    'brand' => 'Phonix',
                    'short_desc' => $course['short_desc'],
                    'description' => $course['description'],
                    'price' => $course['price'],
                    'sale_price' => $course['sale_price'],
                    'stock' => $course['stock'],
                    'image' => $course['image'],
                    'gallery' => null,
                    'is_featured' => true,
                    'is_active' => true,
                    'views' => 0,
                ]
            );
        }
    }
}
