<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        DB::table('admin_users')->insertOrIgnore([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'full_name' => 'Administrator',
            'email' => 'admin@helmet.vn',
            'role' => 'admin',
            'is_active' => true,
            'created_at' => now(),
        ]);

        // Default settings
        $settings = [
            ['setting_key' => 'site_name',      'setting_value' => 'Helmet CMS'],
            ['setting_key' => 'site_desc',       'setting_value' => 'Chuyên sản xuất mũ bảo hiểm in logo theo yêu cầu doanh nghiệp'],
            ['setting_key' => 'hotline',         'setting_value' => '0909 123 456'],
            ['setting_key' => 'zalo',            'setting_value' => '0909123456'],
            ['setting_key' => 'facebook_url',    'setting_value' => 'https://facebook.com'],
            ['setting_key' => 'email',           'setting_value' => 'info@helmet.vn'],
            ['setting_key' => 'address',         'setting_value' => 'TP. Hồ Chí Minh'],
            ['setting_key' => 'working_hours',   'setting_value' => 'Thứ 2 – Thứ 7: 8:00 – 17:30'],
            ['setting_key' => 'about_title',     'setting_value' => 'Đối tác sản xuất mũ bảo hiểm in logo tin cậy'],
            ['setting_key' => 'about_desc',      'setting_value' => 'Từ 2019, chúng tôi đã đồng hành cùng hơn 500 doanh nghiệp trong việc tạo ra những chiếc mũ bảo hiểm mang thương hiệu riêng.'],
            ['setting_key' => 'shipping_fee',    'setting_value' => '0'],
        ];

        foreach ($settings as $s) {
            DB::table('settings')->insertOrIgnore($s);
        }

        $this->call(SatcSeeder::class);
    }
}
