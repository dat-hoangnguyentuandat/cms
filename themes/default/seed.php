<?php

/**
 * Default Theme — Seed Data (REFERENCE for mass-produced themes)
 *
 * Runs automatically on theme activation (ThemeSeeder). All upserts keyed on
 * slug/name — safe to re-run. menu_items only seeds when menu table is empty.
 *
 * Rules demonstrated:
 * - Settings keys match exactly what the CMS admin can edit (no invented keys).
 * - Image paths use `theme-assets/default/images/...` (no storage/, no leading slash).
 * - category_slug auto-resolves to category_id (never hardcode IDs).
 * - testimonials use field `role` (not `position`).
 * - menu_items link ONLY to existing routes (no /uu-dai, /bang-gia, /dat-lich).
 */
return [

    'settings' => [
        'site_name'     => 'Helmet Store',
        'site_tagline'  => 'Giải pháp toàn diện',
        'site_desc'     => 'Helmet Store cung cấp sản phẩm và dịch vụ chất lượng cao, đồng hành cùng doanh nghiệp phát triển bền vững.',
        'hotline'       => '0909 123 456',
        'email'         => 'contact@helmetstore.vn',
        'address'       => '123 Nguyễn Huệ, Quận 1, TP. Hồ Chí Minh',
        'working_hours' => '08:00 - 18:00 (Thứ 2 - Thứ 7)',
        'facebook_url'  => 'https://facebook.com/helmetstore',
        'youtube_url'   => 'https://youtube.com/@helmetstore',
        'instagram_url' => 'https://instagram.com/helmetstore',
        'zalo'          => '0909123456',
        'about_title'   => 'Đối Tác Tin Cậy Của Doanh Nghiệp',
        'about_desc'    => '<p>Với nhiều năm kinh nghiệm, Helmet Store cung cấp sản phẩm và dịch vụ chất lượng cao, đáp ứng mọi nhu cầu của khách hàng với sự tận tâm và chuyên nghiệp.</p>',
        'hero_heading'  => 'Giải Pháp Toàn Diện Cho Doanh Nghiệp',
        'hero_lead'     => 'Đồng hành cùng bạn phát triển',
        'hero_desc'     => 'Sản phẩm và dịch vụ chất lượng cao, uy tín hàng đầu, được hàng nghìn khách hàng tin tưởng.',
    ],

    'categories' => [
        ['name' => 'Sản Phẩm Nổi Bật', 'slug' => 'san-pham-noi-bat', 'sort_order' => 1],
        ['name' => 'Khuyến Mãi',        'slug' => 'khuyen-mai',        'sort_order' => 2],
        ['name' => 'Phụ Kiện',          'slug' => 'phu-kien',          'sort_order' => 3],
        ['name' => 'Tin Công Ty',       'slug' => 'tin-cong-ty',       'sort_order' => 4],
        ['name' => 'Cẩm Nang',          'slug' => 'cam-nang',          'sort_order' => 5],
    ],

    'menu_items' => [
        ['label' => 'Trang chủ',  'url' => '/',           'type' => 'custom',       'sort_order' => 1],
        ['label' => 'Giới thiệu', 'url' => '/gioi-thieu', 'type' => 'page',         'sort_order' => 2],
        ['label' => 'Sản phẩm',   'url' => '/san-pham',   'type' => 'product_list', 'sort_order' => 3, 'children' => [
            ['label' => 'Sản Phẩm Nổi Bật', 'type' => 'product_cat', 'slug' => 'san-pham-noi-bat', 'sort_order' => 1],
            ['label' => 'Khuyến Mãi',        'type' => 'product_cat', 'slug' => 'khuyen-mai',        'sort_order' => 2],
            ['label' => 'Phụ Kiện',          'type' => 'product_cat', 'slug' => 'phu-kien',          'sort_order' => 3],
        ]],
        ['label' => 'Tin tức',  'url' => '/tin-tuc', 'type' => 'news_list', 'sort_order' => 4],
        ['label' => 'Liên hệ',  'url' => '/lien-he', 'type' => 'contact',   'sort_order' => 5],
        ['label' => 'Đánh giá', 'url' => '/danh-gia','type' => 'custom',    'sort_order' => 6],
    ],

    'products' => [
        [
            'category_slug' => 'san-pham-noi-bat',
            'name'          => 'Sản Phẩm Tiêu Biểu 01',
            'slug'          => 'san-pham-tieu-bieu-01',
            'short_desc'    => 'Sản phẩm chất lượng cao, thiết kế hiện đại, phù hợp nhu cầu doanh nghiệp.',
            'description'   => '<p>Mô tả chi tiết sản phẩm tiêu biểu 01 — chất lượng đảm bảo, bảo hành chính hãng, hỗ trợ tận tâm.</p>',
            'price'         => 1500000,
            'image'         => 'theme-assets/default/images/products/product1.jpg',
            'is_featured'   => true,
            'is_active'     => true,
        ],
        [
            'category_slug' => 'san-pham-noi-bat',
            'name'          => 'Sản Phẩm Tiêu Biểu 02',
            'slug'          => 'san-pham-tieu-bieu-02',
            'short_desc'    => 'Giải pháp tối ưu cho doanh nghiệp với chi phí hợp lý.',
            'description'   => '<p>Mô tả chi tiết sản phẩm tiêu biểu 02 — hiệu năng cao, độ bền vượt trội.</p>',
            'price'         => 2300000,
            'image'         => 'theme-assets/default/images/products/product2.jpg',
            'is_featured'   => true,
            'is_active'     => true,
        ],
        [
            'category_slug' => 'khuyen-mai',
            'name'          => 'Sản Phẩm Khuyến Mãi 01',
            'slug'          => 'san-pham-khuyen-mai-01',
            'short_desc'    => 'Ưu đãi đặc biệt — giảm giá sốc trong tháng.',
            'description'   => '<p>Sản phẩm khuyến mãi với mức giá ưu đãi nhất, số lượng có hạn.</p>',
            'price'         => 990000,
            'image'         => 'theme-assets/default/images/products/product3.jpg',
            'is_featured'   => true,
            'is_active'     => true,
        ],
        [
            'category_slug' => 'phu-kien',
            'name'          => 'Phụ Kiện Chính Hãng 01',
            'slug'          => 'phu-kien-chinh-hang-01',
            'short_desc'    => 'Phụ kiện chính hãng, tương thích đa dạng sản phẩm.',
            'description'   => '<p>Phụ kiện chính hãng với chất lượng đảm bảo và giá tốt.</p>',
            'price'         => 350000,
            'image'         => 'theme-assets/default/images/products/product4.jpg',
            'is_featured'   => false,
            'is_active'     => true,
        ],
        [
            'category_slug' => 'phu-kien',
            'name'          => 'Phụ Kiện Chính Hãng 02',
            'slug'          => 'phu-kien-chinh-hang-02',
            'short_desc'    => 'Thiết kế nhỏ gọn, tiện lợi, độ bền cao.',
            'description'   => '<p>Phụ kiện chất lượng, được nhiều khách hàng lựa chọn.</p>',
            'price'         => 480000,
            'image'         => 'theme-assets/default/images/products/product5.jpg',
            'is_featured'   => false,
            'is_active'     => true,
        ],
        [
            'category_slug' => 'san-pham-noi-bat',
            'name'          => 'Sản Phẩm Cao Cấp 03',
            'slug'          => 'san-pham-cao-cap-03',
            'short_desc'    => 'Dòng sản phẩm cao cấp dành cho khách hàng doanh nghiệp.',
            'description'   => '<p>Sản phẩm cao cấp với công nghệ tiên tiến, bảo hành dài hạn.</p>',
            'price'         => 4500000,
            'image'         => 'theme-assets/default/images/products/product6.jpg',
            'is_featured'   => true,
            'is_active'     => true,
        ],
    ],

    'news' => [
        [
            'category_slug' => 'tin-cong-ty',
            'title'        => 'Helmet Store khai trương chi nhánh mới',
            'slug'         => 'helmet-store-khai-truong-chi-nhanh-moi',
            'excerpt'      => 'Mở rộng quy mô phục vụ khách hàng với chi nhánh mới tại trung tâm thành phố.',
            'content'      => '<p>Helmet Store vui mừng thông báo khai trương chi nhánh mới, mang đến trải nghiệm mua sắm và dịch vụ tốt hơn cho khách hàng.</p>',
            'image'        => 'theme-assets/default/images/news/news1.jpg',
            'is_published' => true,
        ],
        [
            'category_slug' => 'cam-nang',
            'title'        => 'Cẩm nang lựa chọn sản phẩm phù hợp',
            'slug'         => 'cam-nang-lua-chon-san-pham-phu-hop',
            'excerpt'      => 'Những tiêu chí quan trọng giúp bạn chọn được sản phẩm ưng ý nhất.',
            'content'      => '<p>Bài viết chia sẻ các tiêu chí lựa chọn sản phẩm: chất lượng, giá cả, bảo hành và dịch vụ hậu mãi.</p>',
            'image'        => 'theme-assets/default/images/news/news2.jpg',
            'is_published' => true,
        ],
        [
            'category_slug' => 'khuyen-mai',
            'title'        => 'Chương trình khuyến mãi lớn trong tháng',
            'slug'         => 'chuong-trinh-khuyen-mai-lon-trong-thang',
            'excerpt'      => 'Hàng loạt ưu đãi hấp dẫn dành cho khách hàng thân thiết.',
            'content'      => '<p>Đừng bỏ lỡ chương trình khuyến mãi lớn nhất trong tháng với mức giảm giá lên đến 50%.</p>',
            'image'        => 'theme-assets/default/images/news/news3.jpg',
            'is_published' => true,
        ],
    ],

    'testimonials' => [
        [
            'name'    => 'Anh Nguyễn Văn An',
            'role'    => 'Giám đốc — Công ty TNHH ABC',
            'content' => 'Sản phẩm chất lượng, dịch vụ chuyên nghiệp. Chúng tôi rất hài lòng và sẽ tiếp tục hợp tác lâu dài.',
            'rating'  => 5,
        ],
        [
            'name'    => 'Chị Trần Thị Bình',
            'role'    => 'Chủ cửa hàng',
            'content' => 'Giao hàng nhanh, đóng gói cẩn thận, hỗ trợ nhiệt tình. Rất đáng tin cậy!',
            'rating'  => 5,
        ],
        [
            'name'    => 'Anh Lê Hoàng Cường',
            'role'    => 'Khách hàng thân thiết',
            'content' => 'Đã mua nhiều lần và luôn nhận được sản phẩm đúng như mô tả. Giá cả hợp lý, chất lượng tốt.',
            'rating'  => 5,
        ],
    ],
];
