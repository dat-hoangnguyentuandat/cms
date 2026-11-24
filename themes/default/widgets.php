<?php

/**
 * Default Theme — Widget Registry (REFERENCE THEME for mass production)
 *
 * Rules this file demonstrates:
 * - Every block_type in `defaults` is ADMIN-EDITABLE (has validatedSettings()
 *   branch + a form in resources/views/admin/homepage-layout/_blocks.blade.php).
 *   This is what makes the homepage fully customizable in the CMS.
 * - Every default carries a `settings` array mirroring the keys the widget blade
 *   reads, so the seeded layout looks right AND the admin form is pre-filled.
 * - `style_key` matches a real file in public/admin-assets/widgets/{type}/{key}.blade.php.
 * - All standard admin zones declared (incl. footer_top + float_*).
 */
return [

    'zones' => [
        'header'        => ['label' => 'Header'],
        'main'          => ['label' => 'Trang chủ'],
        'sidebar'       => ['label' => 'Sidebar'],
        'footer_top'    => ['label' => 'Footer trên'],
        'footer_1'      => ['label' => 'Footer 1'],
        'footer_2'      => ['label' => 'Footer 2'],
        'footer_3'      => ['label' => 'Footer 3'],
        'footer_bottom' => ['label' => 'Footer dưới'],
        'float_left'    => ['label' => 'Trái'],
        'float_right'   => ['label' => 'Phải'],
    ],

    'widgets' => [
        'header'                  => ['label' => 'Header',                'allowed_zones' => ['header']],
        'topbar'                  => ['label' => 'Topbar',                'allowed_zones' => ['header']],
        'slider'                  => ['label' => 'Slider',                'allowed_zones' => ['main']],
        'banner'                  => ['label' => 'Banner',                'allowed_zones' => ['header', 'main']],
        'service-section'         => ['label' => 'Khối dịch vụ',          'allowed_zones' => ['main']],
        'service-block'           => ['label' => 'Khối dịch vụ (rich)',   'allowed_zones' => ['main']],
        'about'                   => ['label' => 'Về chúng tôi',          'allowed_zones' => ['main']],
        'stats'                   => ['label' => 'Thống kê',              'allowed_zones' => ['main']],
        'product-list'            => ['label' => 'Sản phẩm theo Thể loại','allowed_zones' => ['main', 'sidebar']],
        'product-group'           => ['label' => 'Nhóm sản phẩm',         'allowed_zones' => ['main', 'sidebar']],
        'post-group'              => ['label' => 'Nhóm bài viết',         'allowed_zones' => ['main', 'sidebar']],
        'partners'                => ['label' => 'Đối tác',               'allowed_zones' => ['main']],
        'why-choose'              => ['label' => 'Vì sao chọn',           'allowed_zones' => ['main']],
        'process'                 => ['label' => 'Quy trình',             'allowed_zones' => ['main']],
        'customer-testimonials'   => ['label' => 'Cảm nhận khách hàng',   'allowed_zones' => ['main']],
        'contact-form'            => ['label' => 'Form liên hệ',          'allowed_zones' => ['main']],
        'newsletter'              => ['label' => 'Form tư vấn / Đăng ký', 'allowed_zones' => ['main', 'sidebar', 'footer_1', 'footer_2', 'footer_3']],
        'product-filter'          => ['label' => 'Bộ lọc sản phẩm',       'allowed_zones' => ['sidebar']],
        'list-of-articles-by-category' => ['label' => 'Bài viết theo Thể loại', 'allowed_zones' => ['main', 'sidebar']],
        'editor'                  => ['label' => 'Khung soạn thảo',       'allowed_zones' => ['footer_1', 'footer_2', 'footer_3'], 'settings_schema' => [
            'content' => ['type' => 'textarea', 'label' => 'Nội dung HTML', 'rows' => 6, 'max' => 20000, 'placeholder' => '<p>Nội dung tùy ý, hỗ trợ HTML</p>'],
        ]],
        'menu'                    => ['label' => 'Menu',                  'allowed_zones' => ['footer_1', 'footer_2', 'footer_3'], 'settings_schema' => [
            'items' => ['type' => 'repeater', 'label' => 'Liên kết', 'item_label' => 'LIÊN KẾT', 'fields' => [
                'label' => ['type' => 'text', 'label' => 'Nhãn', 'max' => 120],
                'url' => ['type' => 'url', 'label' => 'URL', 'max' => 255, 'placeholder' => 'https://...'],
            ]],
        ]],
        'social-links'            => ['label' => 'Liên kết mạng xã hội',  'allowed_zones' => ['footer_1', 'footer_2', 'footer_3']],
        'fanpage'                 => ['label' => 'Fanpage FB',            'allowed_zones' => ['sidebar', 'footer_1', 'footer_2', 'footer_3']],
        'footer'                  => ['label' => 'Footer dưới',           'allowed_zones' => ['footer_bottom'], 'settings_schema' => [
            'copyright' => ['type' => 'text', 'label' => 'Dòng bản quyền', 'max' => 255],
            'items' => ['type' => 'repeater', 'label' => 'Liên kết', 'item_label' => 'LIÊN KẾT', 'fields' => [
                'label' => ['type' => 'text', 'label' => 'Nhãn', 'max' => 120],
                'url' => ['type' => 'url', 'label' => 'URL', 'max' => 255, 'placeholder' => 'https://...'],
            ]],
        ]],
        'floating-action-buttons' => ['label' => 'Nút hành động nổi',     'allowed_zones' => ['float_left', 'float_right']],
    ],

    /**
     * Default homepage layout. Every block_type below is admin-editable.
     * `settings` mirror the keys each widget blade reads (see SKILL "Ma Trận Widget").
     */
    'defaults' => [
        ['zone' => 'header', 'block_type' => 'header', 'style_key' => 'menu_classic', 'title' => 'Header', 'settings' => [
            'show_hotline' => true,
            'show_cta'     => true,
            'cta_text'     => 'Liên hệ ngay',
            'cta_url'      => '/lien-he',
        ]],

        ['zone' => 'main', 'block_type' => 'slider', 'style_key' => 'slider_1', 'title' => 'Slider', 'settings' => [
            'autoplay' => true,
            'items'    => [
                ['image' => 'theme-assets/default/images/slides/slide1.jpg', 'heading' => 'Giải Pháp Toàn Diện Cho Doanh Nghiệp', 'sub' => 'Sản phẩm & dịch vụ chất lượng cao, đồng hành cùng bạn phát triển bền vững.', 'url' => '/san-pham'],
                ['image' => 'theme-assets/default/images/slides/slide2.jpg', 'heading' => 'Cam Kết Chất Lượng — Uy Tín Hàng Đầu', 'sub' => 'Hàng nghìn khách hàng tin tưởng lựa chọn và đồng hành dài lâu.', 'url' => '/lien-he'],
            ],
        ]],

        ['zone' => 'main', 'block_type' => 'service-section', 'style_key' => 'service_section_1', 'title' => 'Dịch vụ nổi bật', 'settings' => [
            'subtitle' => 'Các dịch vụ được khách hàng tin tưởng và đánh giá cao nhất.',
            'limit'    => 6,
        ]],

        ['zone' => 'main', 'block_type' => 'about', 'style_key' => 'about_1', 'title' => 'Về chúng tôi', 'settings' => [
            'heading'     => 'Đối Tác Tin Cậy Của Doanh Nghiệp',
            'subheading'  => 'Về chúng tôi',
            'description' => 'Với nhiều năm kinh nghiệm, chúng tôi cung cấp sản phẩm và dịch vụ chất lượng cao, đáp ứng mọi nhu cầu của khách hàng với sự tận tâm và chuyên nghiệp.',
            'image'       => 'theme-assets/default/images/about.jpg',
            'cta_text'    => 'Tìm hiểu thêm',
            'cta_link'    => '/gioi-thieu',
            'stats'       => [
                ['value' => '5+',     'label' => 'Năm kinh nghiệm'],
                ['value' => '500+',   'label' => 'Khách hàng'],
                ['value' => '1.000+', 'label' => 'Đơn hàng'],
                ['value' => '4.9★',   'label' => 'Đánh giá'],
            ],
        ]],

        ['zone' => 'main', 'block_type' => 'product-group', 'style_key' => 'product_group_1', 'title' => 'Sản phẩm nổi bật', 'settings' => [
            'subtitle' => 'Những sản phẩm bán chạy và được đánh giá cao nhất.',
            'limit_per_category' => 8,
            'show_all_tab' => true,
        ]],

        ['zone' => 'main', 'block_type' => 'customer-testimonials', 'style_key' => 'customer-testimonials_4', 'title' => 'Khách hàng nói gì về chúng tôi', 'settings' => [
            'subtitle'       => 'Trải nghiệm thực tế từ những khách hàng đã đồng hành cùng chúng tôi.',
            'layout'         => 'slider',
            'content_source' => 'import',
            'source_limit'   => 6,
            'autoplay'       => true,
        ]],

        ['zone' => 'main', 'block_type' => 'post-group', 'style_key' => 'post_group_1', 'title' => 'Tin tức mới nhất', 'settings' => [
            'subtitle' => 'Cập nhật tin tức, kiến thức và xu hướng mới nhất.',
            'limit'    => 3,
        ]],

        ['zone' => 'main', 'block_type' => 'newsletter', 'style_key' => 'newsletter_1', 'title' => 'Sẵn sàng bắt đầu?', 'settings' => [
            'subtitle' => 'Để lại thông tin, đội ngũ của chúng tôi sẽ liên hệ tư vấn miễn phí trong 24 giờ.',
            'btn_text' => 'Gửi yêu cầu tư vấn',
            'subject'  => 'Yêu cầu tư vấn từ trang chủ',
            'items'    => [
                ['text' => 'Tư vấn miễn phí'],
                ['text' => 'Hỗ trợ 24/7'],
                ['text' => 'Báo giá nhanh chóng'],
            ],
        ]],

        ['zone' => 'footer_1', 'block_type' => 'editor', 'style_key' => 'editor_1', 'title' => 'Về chúng tôi', 'settings' => [
            'content' => '',
        ]],

        ['zone' => 'footer_2', 'block_type' => 'menu', 'style_key' => 'menu_1', 'title' => 'Liên kết', 'settings' => [
            'items' => [
                ['label' => 'Trang chủ',  'url' => '/'],
                ['label' => 'Giới thiệu', 'url' => '/gioi-thieu'],
                ['label' => 'Sản phẩm',   'url' => '/san-pham'],
                ['label' => 'Tin tức',    'url' => '/tin-tuc'],
                ['label' => 'Liên hệ',    'url' => '/lien-he'],
            ],
        ]],

        ['zone' => 'footer_3', 'block_type' => 'social-links', 'style_key' => 'social_links_1', 'title' => 'Kết nối với chúng tôi', 'settings' => []],

        ['zone' => 'footer_bottom', 'block_type' => 'footer', 'style_key' => 'footer_1', 'title' => 'Footer', 'settings' => []],

        ['zone' => 'float_right', 'block_type' => 'floating-action-buttons', 'style_key' => 'fab_1', 'title' => 'Nút nổi', 'settings' => [
            'position' => 'middle',
        ]],
    ],

];
