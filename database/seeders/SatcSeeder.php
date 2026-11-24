<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Faq;
use App\Models\MenuItem;
use App\Models\News;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SatcSeeder extends Seeder
{
    public function run(): void
    {
        $this->clearThemeContent();
        $this->settings();
        $categoryIds = $this->categories();
        $this->services($categoryIds);
        $this->news($categoryIds);
        $this->testimonials();
        $this->faqs();
        $this->menuItems();
    }

    private function clearThemeContent(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Service::query()->truncate();
        News::query()->truncate();
        Category::query()->truncate();
        Testimonial::query()->truncate();
        Faq::query()->truncate();
        MenuItem::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function settings(): void
    {
        $settings = [
            'active_theme' => 'satc',
            'site_name' => 'SATC.VN',
            'site_tagline' => 'GIẢI PHÁP CÔNG NGHỆ & AN NINH TOÀN DIỆN',
            'site_desc' => 'SATC.VN cung cấp giải pháp, thiết bị và dịch vụ tích hợp hệ thống công nghệ thông tin & an ninh, giúp doanh nghiệp vận hành an toàn, hiệu quả và sẵn sàng cho tương lai.',
            'hero_heading' => 'GIẢI PHÁP CÔNG NGHỆ & AN NINH TOÀN DIỆN',
            'hero_lead' => 'Đồng hành cùng Doanh nghiệp trong kỷ nguyên số',
            'hero_desc' => 'SATC.VN cung cấp giải pháp, thiết bị và dịch vụ tích hợp hệ thống công nghệ thông tin & an ninh, giúp doanh nghiệp vận hành an toàn, hiệu quả và sẵn sàng cho tương lai.',
            'hotline' => '024 9999 8888',
            'zalo' => '0249999888',
            'facebook_url' => 'https://facebook.com/satc.vn',
            'youtube_url' => 'https://youtube.com/@satcvn',
            'email' => 'info@satc.vn',
            'address' => 'Tầng 5, Tòa nhà SATC, 123 Lê Lợi, Quận 1, TP. Hồ Chí Minh',
            'working_hours' => 'Thứ 2 – Thứ 7: 8:00 – 17:30',
            'about_title' => 'Đối tác công nghệ đáng tin cậy của doanh nghiệp Việt',
            'about_desc' => '<p>SATC.VN định vị là đơn vị tích hợp giải pháp công nghệ thông tin và an ninh cho doanh nghiệp, tập trung vào các hệ thống camera giám sát, mạng LAN/WiFi, kiểm soát ra vào, báo động, tổng đài IP và thiết bị văn phòng.</p><p>Đội ngũ kỹ thuật của chúng tôi làm việc theo quy trình khảo sát hiện trạng, thiết kế phương án, triển khai thiết bị chính hãng, nghiệm thu thực tế và hỗ trợ sau bàn giao. Cách tiếp cận này giúp doanh nghiệp có một hạ tầng vận hành ổn định, dễ mở rộng và an toàn hơn.</p><p>SATC.VN phù hợp với các mô hình nhà máy, văn phòng, trường học, khách sạn, kho vận và chuỗi cơ sở cần chuẩn hóa hạ tầng công nghệ trên nhiều điểm triển khai.</p>',
            'maps_embed' => '',
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }
    }

    private function categories(): array
    {
        $categories = [
            ['name' => 'Camera giám sát',      'slug' => 'camera-giam-sat',      'sort_order' => 1,  'description' => 'Camera IP, AI Camera, quan sát tập trung và giám sát từ xa.'],
            ['name' => 'Mạng & WiFi',           'slug' => 'mang-wifi',            'sort_order' => 2,  'description' => 'Thiết kế LAN/WAN, WiFi doanh nghiệp, switch, router, firewall.'],
            ['name' => 'Kiểm soát ra vào',      'slug' => 'kiem-soat-ra-vao',     'sort_order' => 3,  'description' => 'Máy chấm công, Face ID, khóa từ, thẻ từ, barrier và flap gate.'],
            ['name' => 'Hệ thống báo động',     'slug' => 'he-thong-bao-dong',    'sort_order' => 4,  'description' => 'Báo trộm, cảm biến cửa, cảm biến chuyển động, báo cháy và báo khói.'],
            ['name' => 'Tổng đài điện thoại',   'slug' => 'tong-dai-dien-thoai',  'sort_order' => 5,  'description' => 'Tổng đài IP PBX, SIP Phone, SIP Trunk, Call Center, Voicemail.'],
            ['name' => 'Thiết bị văn phòng',    'slug' => 'thiet-bi-van-phong',   'sort_order' => 6,  'description' => 'Máy in, laptop, workstation, máy chiếu và phần mềm bản quyền.'],
            ['name' => 'Tin công ty',           'slug' => 'tin-cong-ty',          'sort_order' => 41, 'description' => 'Cập nhật hoạt động SATC.VN.'],
            ['name' => 'Kiến thức công nghệ',   'slug' => 'kien-thuc-cong-nghe',  'sort_order' => 42, 'description' => 'Kiến thức camera, mạng, tổng đài, an ninh.'],
            ['name' => 'Hướng dẫn',             'slug' => 'huong-dan',            'sort_order' => 43, 'description' => 'Hướng dẫn sử dụng và vận hành hệ thống.'],
        ];

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

    private function services(array $categoryIds): void
    {
        $services = [
            [
                'category' => 'camera-giam-sat',
                'name' => 'Hệ thống Camera giám sát',
                'slug' => 'he-thong-camera-giam-sat',
                'short_desc' => "Camera IP, AI Camera\nQuan sát tập trung\nLưu trữ & quản lý dữ liệu\nGiám sát từ xa mọi lúc",
                'description' => '<p>Giải pháp camera giám sát doanh nghiệp giúp theo dõi hoạt động vận hành, bảo vệ tài sản và nâng cao khả năng phản ứng sự cố.</p><ul><li>Khảo sát điểm lắp đặt, góc quan sát và vùng rủi ro.</li><li>Thiết kế hệ thống camera IP/AI Camera, NVR và lưu trữ tập trung.</li><li>Cấu hình giám sát từ xa trên máy tính, điện thoại và trung tâm điều hành.</li><li>Bảo hành thiết bị chính hãng, hỗ trợ kỹ thuật 24/7.</li></ul>',
                'price' => 0,
                'duration' => '3 - 10 ngày tùy quy mô',
                'image' => 'theme-assets/satc/images/solutions/camera.jpg',
                'is_featured' => true,
            ],
            [
                'category' => 'mang-wifi',
                'name' => 'Hạ tầng Mạng & WiFi',
                'slug' => 'ha-tang-mang-wifi',
                'short_desc' => "Thiết kế & triển khai LAN, WAN\nWiFi doanh nghiệp, WiFi Mesh\nSwitch, Router, Firewall\nCáp mạng, Cáp quang",
                'description' => '<p>Hạ tầng mạng ổn định là nền tảng cho mọi hoạt động chuyển đổi số. SATC.VN thiết kế hệ thống LAN/WAN, WiFi và bảo mật mạng theo đúng nhu cầu thực tế.</p><ul><li>Khảo sát mặt bằng và tải sử dụng.</li><li>Thiết kế sơ đồ mạng, tủ rack, patch panel và tuyến cáp.</li><li>Triển khai WiFi roaming, VLAN, firewall và giám sát thiết bị.</li><li>Nghiệm thu tốc độ, vùng phủ và độ ổn định.</li></ul>',
                'price' => 0,
                'duration' => '5 - 15 ngày tùy quy mô',
                'image' => 'theme-assets/satc/images/solutions/network.jpg',
                'is_featured' => true,
            ],
            [
                'category' => 'kiem-soat-ra-vao',
                'name' => 'Kiểm soát ra vào',
                'slug' => 'kiem-soat-ra-vao-doanh-nghiep',
                'short_desc' => "Máy chấm công, Face ID\nKhóa từ, khóa vân tay\nThẻ từ, thẻ mã QR\nCổng barrier, Flap gate",
                'description' => '<p>Giải pháp kiểm soát ra vào giúp doanh nghiệp quản lý người ra vào, tích hợp chấm công và tăng cường an ninh khu vực trọng yếu.</p><ul><li>Face ID, vân tay, thẻ từ, QR code.</li><li>Khóa từ, cửa kính, barrier, flap gate.</li><li>Phân quyền theo người dùng, thời gian và khu vực.</li><li>Báo cáo ra vào và tích hợp phần mềm nhân sự nếu cần.</li></ul>',
                'price' => 0,
                'duration' => '2 - 7 ngày tùy điểm kiểm soát',
                'image' => 'theme-assets/satc/images/solutions/access.jpg',
                'is_featured' => true,
            ],
            [
                'category' => 'he-thong-bao-dong',
                'name' => 'Hệ thống Báo động',
                'slug' => 'he-thong-bao-dong-doanh-nghiep',
                'short_desc' => "Báo trộm, báo động không dây\nCảm biến cửa, chuyển động\nCòi báo động, trung tâm báo động\nBáo cháy, báo khói",
                'description' => '<p>Hệ thống báo động giúp phát hiện sớm xâm nhập, sự cố cháy nổ và các rủi ro vận hành trong doanh nghiệp.</p><ul><li>Cảm biến cửa, cảm biến chuyển động, cảm biến khói.</li><li>Trung tâm báo động có dây/không dây.</li><li>Còi, đèn báo, cảnh báo qua ứng dụng.</li><li>Kịch bản cảnh báo theo từng khu vực.</li></ul>',
                'price' => 0,
                'duration' => '2 - 6 ngày tùy quy mô',
                'image' => 'theme-assets/satc/images/solutions/alarm.jpg',
                'is_featured' => true,
            ],
            [
                'category' => 'tong-dai-dien-thoai',
                'name' => 'Tổng đài Điện thoại',
                'slug' => 'tong-dai-dien-thoai-ip',
                'short_desc' => "Tổng đài IP PBX\nĐiện thoại IP, SIP Phone\nSIP Trunk, Call Center\nConference, Voicemail",
                'description' => '<p>Tổng đài IP giúp doanh nghiệp tối ưu chi phí liên lạc, quản lý cuộc gọi và nâng cao trải nghiệm chăm sóc khách hàng.</p><ul><li>IP PBX, SIP Trunk, IVR, ghi âm cuộc gọi.</li><li>Điện thoại IP, softphone, call center.</li><li>Phân quyền, báo cáo, voicemail và conference.</li><li>Tích hợp CRM theo nhu cầu.</li></ul>',
                'price' => 0,
                'duration' => '2 - 8 ngày tùy số máy nhánh',
                'image' => 'theme-assets/satc/images/solutions/pbx.jpg',
                'is_featured' => true,
            ],
            [
                'category' => 'thiet-bi-van-phong',
                'name' => 'Thiết bị Văn phòng',
                'slug' => 'thiet-bi-van-phong-doanh-nghiep',
                'short_desc' => "Máy in, Laptop, Workstation\nMáy in, Máy photocopy\nMáy chiếu, màn chiếu\nPhần mềm bản quyền",
                'description' => '<p>SATC.VN cung cấp thiết bị văn phòng chính hãng, hỗ trợ tư vấn cấu hình, triển khai và bảo hành cho doanh nghiệp.</p><ul><li>Laptop, workstation, máy in, photocopy.</li><li>Máy chiếu, màn chiếu, thiết bị hội họp.</li><li>Phần mềm bản quyền Microsoft, bảo mật và văn phòng.</li><li>Tư vấn lựa chọn theo ngân sách và mục tiêu sử dụng.</li></ul>',
                'price' => 0,
                'duration' => 'Giao hàng & triển khai theo yêu cầu',
                'image' => 'theme-assets/satc/images/solutions/office.jpg',
                'is_featured' => true,
            ],
        ];

        foreach ($services as $data) {
            $categorySlug = $data['category'];
            unset($data['category']);
            Service::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, [
                    'category_id' => $categoryIds[$categorySlug],
                    'is_active' => true,
                ])
            );
        }
    }

    private function news(array $categoryIds): void
    {
        $newsImages = [
            '5-tieu-chi-chon-camera-giam-sat-cho-doanh-nghiep' => 'theme-assets/satc/images/news/news-camera.jpg',
            'thiet-ke-wifi-doanh-nghiep-tranh-diem-chet-va-nghen-mang' => 'theme-assets/satc/images/news/news-wifi.jpg',
            'huong-dan-bao-tri-he-thong-kiem-soat-ra-vao' => 'theme-assets/satc/images/news/news-access.jpg',
            'satc-vn-mo-rong-dich-vu-tich-hop-tong-dai-ip' => 'theme-assets/satc/images/news/news-pbx.jpg',
            'an-ninh-mang-doanh-nghiep-cac-lop-bao-ve-can-trien-khai' => 'theme-assets/satc/images/news/news-security.jpg',
            'face-id-cham-cong-loi-ich-va-luu-y-khi-trien-khai' => 'theme-assets/satc/images/news/news-faceid.jpg',
        ];
        $posts = [
            ['category' => 'kien-thuc-cong-nghe', 'title' => '5 tiêu chí chọn camera giám sát cho doanh nghiệp', 'slug' => '5-tieu-chi-chon-camera-giam-sat-cho-doanh-nghiep', 'excerpt' => 'Doanh nghiệp cần cân nhắc độ phân giải, góc nhìn, lưu trữ, AI analytics và khả năng quản trị tập trung khi đầu tư camera giám sát.'],
            ['category' => 'kien-thuc-cong-nghe', 'title' => 'Thiết kế WiFi doanh nghiệp: tránh điểm chết và nghẽn mạng', 'slug' => 'thiet-ke-wifi-doanh-nghiep-tranh-diem-chet-va-nghen-mang', 'excerpt' => 'WiFi doanh nghiệp cần khảo sát phổ sóng, tính toán số lượng người dùng, roaming và phân tách VLAN để vận hành ổn định.'],
            ['category' => 'tin-cong-ty', 'title' => 'SATC.VN mở rộng dịch vụ tích hợp tổng đài IP', 'slug' => 'satc-vn-mo-rong-dich-vu-tich-hop-tong-dai-ip', 'excerpt' => 'SATC.VN bổ sung dịch vụ tư vấn và triển khai tổng đài IP PBX, SIP Trunk, Call Center cho doanh nghiệp vừa và lớn.'],
            ['category' => 'kien-thuc-cong-nghe', 'title' => 'An ninh mạng doanh nghiệp: các lớp bảo vệ cần triển khai', 'slug' => 'an-ninh-mang-doanh-nghiep-cac-lop-bao-ve-can-trien-khai', 'excerpt' => 'Firewall, segment VLAN, kiểm soát truy cập và sao lưu dữ liệu là bốn lớp tối thiểu giúp doanh nghiệp giảm rủi ro tấn công mạng nội bộ.'],
            ['category' => 'huong-dan', 'title' => 'Face ID chấm công: lợi ích và lưu ý khi triển khai', 'slug' => 'face-id-cham-cong-loi-ich-va-luu-y-khi-trien-khai', 'excerpt' => 'Chấm công bằng Face ID giúp giảm gian lận, tiết kiệm thời gian nhưng cần chú ý ánh sáng, vị trí lắp đặt và đồng bộ dữ liệu nhân sự.'],
        ];

        foreach ($posts as $data) {
            $categorySlug = $data['category'];
            unset($data['category']);
            News::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, [
                    'category_id' => $categoryIds[$categorySlug],
                    'content' => '<p>'.e($data['excerpt']).'</p><h2>Vấn đề doanh nghiệp thường gặp</h2><p>Nhiều doanh nghiệp đầu tư thiết bị rời rạc, thiếu thiết kế tổng thể nên dễ phát sinh điểm nghẽn, khó quản trị và khó mở rộng.</p><h2>Khuyến nghị từ SATC.VN</h2><p>Nên khảo sát thực tế, chuẩn hóa sơ đồ triển khai, lựa chọn thiết bị chính hãng và lập kế hoạch bảo trì ngay từ đầu.</p><ul><li>Ưu tiên khả năng quản trị tập trung.</li><li>Đảm bảo bảo mật, phân quyền và sao lưu dữ liệu.</li><li>Kiểm thử thực tế trước khi nghiệm thu.</li></ul>',
                    'image' => $newsImages[$data['slug']] ?? 'theme-assets/satc/images/news/news-camera.jpg',
                    'author' => 'SATC.VN',
                    'is_published' => true,
                ])
            );
        }
    }

    private function testimonials(): void
    {
        $items = [
            ['name' => 'Nguyễn Minh Quân', 'role' => 'Giám đốc vận hành nhà máy', 'content' => 'SATC.VN triển khai nhanh, tài liệu bàn giao rõ ràng và hỗ trợ rất tốt sau nghiệm thu.', 'rating' => 5, 'sort_order' => 1],
            ['name' => 'Trần Hoài An', 'role' => 'IT Manager', 'content' => 'Hệ thống WiFi và tổng đài IP hoạt động ổn định, đội kỹ thuật phản hồi nhanh khi cần hỗ trợ.', 'rating' => 5, 'sort_order' => 2],
            ['name' => 'Lê Phương Thảo', 'role' => 'Quản lý cơ sở giáo dục', 'content' => 'Giải pháp camera và kiểm soát ra vào giúp trường quản lý an ninh tốt hơn rất nhiều.', 'rating' => 5, 'sort_order' => 3],
        ];

        foreach ($items as $item) {
            Testimonial::updateOrCreate(
                ['name' => $item['name']],
                array_merge($item, ['is_active' => true, 'avatar' => null])
            );
        }
    }

    private function faqs(): void
    {
        $items = [
            ['question' => 'SATC.VN có khảo sát miễn phí không?', 'answer' => 'Có. Đội ngũ kỹ thuật có thể khảo sát nhu cầu và hiện trạng để đề xuất giải pháp phù hợp trước khi báo giá.', 'sort_order' => 1],
            ['question' => 'Thiết bị có chính hãng và bảo hành không?', 'answer' => 'SATC.VN ưu tiên thiết bị chính hãng từ các thương hiệu như Hikvision, Dahua, Ruijie, TP-Link, Grandstream, Yeastar, Dell, HP và Microsoft.', 'sort_order' => 2],
            ['question' => 'Có hỗ trợ sau khi bàn giao không?', 'answer' => 'Có. Chúng tôi có chính sách bảo trì định kỳ và hỗ trợ kỹ thuật 24/7 cho các hệ thống đã triển khai.', 'sort_order' => 3],
            ['question' => 'Dự án nhỏ có nhận triển khai không?', 'answer' => 'Có. SATC.VN tư vấn giải pháp theo quy mô thực tế, từ văn phòng nhỏ đến nhà máy, trường học, khách sạn và kho vận.', 'sort_order' => 4],
        ];

        foreach ($items as $item) {
            Faq::updateOrCreate(
                ['question' => $item['question']],
                array_merge($item, ['is_active' => true])
            );
        }
    }

    private function menuItems(): void
    {
        // Parents
        $parents = [
            [
                'label' => 'Trang chủ',
                'type' => 'custom',
                'slug' => '',
                'keyword' => 'giải pháp công nghệ an ninh, giai phap cong nghe an ninh',
                'title' => 'SATC.VN – Giải Pháp Công Nghệ & An Ninh Toàn Diện',
                'description' => 'SATC.VN cung cấp giải pháp, thiết bị và dịch vụ tích hợp hệ thống công nghệ thông tin & an ninh, giúp doanh nghiệp vận hành an toàn, hiệu quả và sẵn sàng cho tương lai.',
                'seo_title' => 'SATC.VN – Giải Pháp Công Nghệ & An Ninh Toàn Diện',
                'seo_description' => 'SATC.VN cung cấp giải pháp camera, mạng, kiểm soát ra vào, báo động và tổng đài IP cho doanh nghiệp. Khảo sát miễn phí, thiết bị chính hãng.',
                'url' => '/',
                'sort_order' => 1,
                'children' => [],
            ],
            [
                'label' => 'Dịch vụ',
                'type' => 'service_list',
                'slug' => 'dich-vu',
                'keyword' => 'dịch vụ công nghệ an ninh, dich vu cong nghe an ninh',
                'title' => 'Dịch Vụ Tích Hợp Hệ Thống Công Nghệ & An Ninh',
                'description' => 'SATC.VN cung cấp đầy đủ các dịch vụ tích hợp hệ thống từ camera giám sát, mạng & WiFi, kiểm soát ra vào, báo động đến tổng đài IP và thiết bị văn phòng.',
                'seo_title' => 'Dịch Vụ Công Nghệ & An Ninh – SATC.VN',
                'seo_description' => 'Khám phá các dịch vụ tích hợp hệ thống của SATC.VN: camera, mạng WiFi, kiểm soát ra vào, báo động, tổng đài IP. Tư vấn & khảo sát miễn phí.',
                'sort_order' => 2,
                'children' => [
                    ['label' => 'Camera giám sát',    'type' => 'service_cat', 'slug' => 'camera-giam-sat',    'keyword' => 'camera giám sát doanh nghiệp, camera giam sat doanh nghiep',    'seo_title' => 'Camera Giám Sát Doanh Nghiệp – SATC.VN',    'seo_description' => 'Giải pháp camera IP, AI Camera, lưu trữ tập trung và giám sát từ xa cho doanh nghiệp.', 'sort_order' => 1],
                    ['label' => 'Mạng & WiFi',        'type' => 'service_cat', 'slug' => 'mang-wifi',          'keyword' => 'hạ tầng mạng wifi doanh nghiệp, ha tang mang wifi doanh nghiep',  'seo_title' => 'Hạ Tầng Mạng & WiFi Doanh Nghiệp – SATC.VN',  'seo_description' => 'Thiết kế và triển khai LAN, WAN, WiFi doanh nghiệp, switch, router, firewall chính hãng.', 'sort_order' => 2],
                    ['label' => 'Kiểm soát ra vào',   'type' => 'service_cat', 'slug' => 'kiem-soat-ra-vao',   'keyword' => 'kiểm soát ra vào doanh nghiệp, kiem soat ra vao doanh nghiep',   'seo_title' => 'Kiểm Soát Ra Vào Doanh Nghiệp – SATC.VN',   'seo_description' => 'Máy chấm công, Face ID, khóa từ, thẻ từ, barrier và flap gate cho doanh nghiệp.', 'sort_order' => 3],
                    ['label' => 'Hệ thống báo động',  'type' => 'service_cat', 'slug' => 'he-thong-bao-dong',  'keyword' => 'hệ thống báo động doanh nghiệp, he thong bao dong doanh nghiep',  'seo_title' => 'Hệ Thống Báo Động Doanh Nghiệp – SATC.VN',  'seo_description' => 'Báo trộm, cảm biến cửa, cảm biến chuyển động, báo cháy và báo khói cho doanh nghiệp.', 'sort_order' => 4],
                    ['label' => 'Tổng đài điện thoại', 'type' => 'service_cat', 'slug' => 'tong-dai-dien-thoai', 'keyword' => 'tổng đài ip doanh nghiệp, tong dai ip doanh nghiep',                'seo_title' => 'Tổng Đài IP Doanh Nghiệp – SATC.VN',         'seo_description' => 'Tổng đài IP PBX, SIP Phone, SIP Trunk, Call Center và Voicemail cho doanh nghiệp.', 'sort_order' => 5],
                    ['label' => 'Thiết bị văn phòng', 'type' => 'service_cat', 'slug' => 'thiet-bi-van-phong', 'keyword' => 'thiết bị văn phòng doanh nghiệp, thiet bi van phong doanh nghiep', 'seo_title' => 'Thiết Bị Văn Phòng Doanh Nghiệp – SATC.VN', 'seo_description' => 'Máy in, laptop, workstation, máy chiếu và phần mềm bản quyền cho doanh nghiệp.', 'sort_order' => 6],
                ],
            ],
            [
                'label' => 'Tin tức',
                'type' => 'news_list',
                'slug' => 'tin-tuc',
                'keyword' => 'tin tức công nghệ an ninh, tin tuc cong nghe an ninh',
                'title' => 'Tin Tức & Kiến Thức Công Nghệ An Ninh',
                'description' => 'Cập nhật tin tức, kiến thức và hướng dẫn về camera giám sát, mạng doanh nghiệp, kiểm soát ra vào và các giải pháp an ninh từ SATC.VN.',
                'seo_title' => 'Tin Tức Công Nghệ & An Ninh – SATC.VN',
                'seo_description' => 'Đọc tin tức, kiến thức công nghệ an ninh mới nhất từ SATC.VN: camera, mạng WiFi, kiểm soát ra vào, tổng đài IP và bảo mật doanh nghiệp.',
                'sort_order' => 4,
                'children' => [
                    ['label' => 'Tin công ty',          'type' => 'news_cat', 'slug' => 'tin-cong-ty',          'keyword' => 'tin tức công ty SATC, tin tuc cong ty SATC',                    'seo_title' => 'Tin Tức Công Ty SATC.VN',                    'seo_description' => 'Cập nhật hoạt động, dự án và thông tin mới nhất từ SATC.VN.', 'sort_order' => 1],
                    ['label' => 'Kiến thức công nghệ',  'type' => 'news_cat', 'slug' => 'kien-thuc-cong-nghe',  'keyword' => 'kiến thức công nghệ an ninh, kien thuc cong nghe an ninh',      'seo_title' => 'Kiến Thức Công Nghệ & An Ninh – SATC.VN',    'seo_description' => 'Kiến thức về camera, mạng, tổng đài, kiểm soát ra vào và an ninh doanh nghiệp.', 'sort_order' => 2],
                    ['label' => 'Hướng dẫn',            'type' => 'news_cat', 'slug' => 'huong-dan',            'keyword' => 'hướng dẫn sử dụng hệ thống, huong dan su dung he thong',        'seo_title' => 'Hướng Dẫn Sử Dụng Hệ Thống – SATC.VN',      'seo_description' => 'Hướng dẫn vận hành, bảo trì và sử dụng các hệ thống công nghệ an ninh.', 'sort_order' => 3],
                ],
            ],
            [
                'label' => 'Liên hệ',
                'type' => 'contact',
                'slug' => 'lien-he',
                'keyword' => 'liên hệ SATC, lien he SATC',
                'title' => 'Liên Hệ SATC.VN – Tư Vấn Giải Pháp Công Nghệ & An Ninh',
                'description' => 'Liên hệ với SATC.VN để được tư vấn miễn phí, khảo sát hiện trạng và nhận báo giá giải pháp công nghệ & an ninh phù hợp với doanh nghiệp của bạn.',
                'seo_title' => 'Liên Hệ SATC.VN – Tư Vấn & Báo Giá Miễn Phí',
                'seo_description' => 'Liên hệ SATC.VN để được tư vấn giải pháp camera, mạng, kiểm soát ra vào và an ninh. Khảo sát miễn phí, phản hồi trong 24 giờ.',
                'sort_order' => 5,
                'children' => [],
            ],
        ];

        foreach ($parents as $data) {
            $children = $data['children'];
            unset($data['children']);

            $parent = MenuItem::updateOrCreate(
                ['type' => $data['type'], 'parent_id' => null],
                array_merge($data, [
                    'target' => '_self',
                    'is_active' => true,
                ])
            );

            foreach ($children as $i => $child) {
                MenuItem::updateOrCreate(
                    ['slug' => $child['slug']],
                    array_merge($child, [
                        'parent_id' => $parent->id,
                        'target' => '_self',
                        'is_active' => true,
                        'title' => $child['seo_title'] ?? $child['label'],
                        'description' => null,
                    ])
                );
            }
        }
    }
}
