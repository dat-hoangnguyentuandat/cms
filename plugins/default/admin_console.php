<?php

return [
    'label' => 'Plugin Mặc định',
    'sections' => [
        'overview' => [
            'label' => 'Tổng quan',
            'description' => 'Điểm vào mặc định cho workspace plugin.',
            'items' => [
                'overview' => [
                    'label' => 'Tổng quan',
                    'description' => 'Trạng thái, hướng dẫn nhanh, đường dẫn quan trọng.',
                    'type' => 'overview',
                    'keywords' => 'dashboard tổng quan trạng thái cấu hình',
                ],
            ],
        ],
        'settings' => [
            'label' => 'Cấu hình',
            'description' => 'Cấu hình toàn plugin lưu bằng PluginSetting/settings_schema.',
            'items' => [
                'general' => [
                    'label' => 'Cấu hình chung',
                    'description' => 'Thông báo, màu sắc và các setting toàn plugin.',
                    'type' => 'settings',
                    'settings_schema' => 'plugin.json:settings_schema',
                    'keywords' => 'setting cấu hình chung màu thông báo',
                ],
            ],
        ],
        'appearance' => [
            'label' => 'Giao diện ngoài site',
            'description' => 'Widget/block do plugin cung cấp cho layout builder.',
            'items' => [
                'announcement_widget' => [
                    'label' => 'Widget thông báo',
                    'description' => 'Block mẫu chỉnh bằng settings_schema trong Bố cục trang chủ.',
                    'type' => 'theme_block',
                    'block_type' => 'default-announcement',
                    'frontend' => [
                        'url' => '/',
                        'selector' => '.default-plugin-announcement',
                    ],
                    'keywords' => 'widget thông báo block trang chủ',
                ],
            ],
        ],
        'data' => [
            'label' => 'Dữ liệu',
            'description' => 'Khai báo resource CRUD khi plugin có dữ liệu nghiệp vụ.',
            'items' => [
                'example_resource' => [
                    'label' => 'Resource mẫu',
                    'description' => 'Đổi table/columns/routes khi plugin cần CRUD riêng.',
                    'type' => 'resource',
                    'table' => null,
                    'columns' => [],
                    'keywords' => 'resource crud table form export',
                ],
            ],
        ],
        'advanced' => [
            'label' => 'Nâng cao',
            'description' => 'Liên kết sang công cụ quản trị CMS có sẵn.',
            'items' => [
                'homepage_layout' => [
                    'label' => 'Bố cục trang chủ',
                    'description' => 'Mở trình quản lý block/widget của CMS.',
                    'type' => 'external_route',
                    'route' => 'admin.homepage-layout.index',
                    'keywords' => 'bố cục trang chủ widget block',
                ],
            ],
        ],
    ],
];
