<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreShortcodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_button_zalo_map_and_contact_form_shortcodes_render(): void
    {
        $html = cms_shortcodes(
            '[button text="Tư vấn ngay" link="/lien-he"] '.
            '[zalo_chat phone="0901234567"] '.
            '[map address="Bình Tân, HCM" height="220"] '.
            '[lead_form source="landing-page"]',
        );

        $this->assertStringContainsString('Tư vấn ngay', $html);
        $this->assertStringContainsString('https://zalo.me/0901234567', $html);
        $this->assertStringContainsString('B%C3%ACnh%20T%C3%A2n%2C%20HCM', $html);
        $this->assertStringContainsString('landing-page', $html);
        $this->assertStringContainsString('data-shortcode-contact-form', $html);
    }

    public function test_product_list_shortcode_renders_active_products_by_category_slug(): void
    {
        $category = Category::create([
            'name' => 'Website',
            'slug' => 'website',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Landing Page',
            'slug' => 'landing-page',
            'short_desc' => 'Short desc',
            'price' => 1000000,
            'stock' => 1,
            'is_active' => true,
        ]);

        $html = cms_shortcodes('[product_list category="website" limit="3"]');

        $this->assertStringContainsString('Landing Page', $html);
        $this->assertStringContainsString(route('product.show', 'landing-page'), $html);
    }
}
