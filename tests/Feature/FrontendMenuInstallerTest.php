<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Services\FrontendMenuInstaller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendMenuInstallerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_only_one_top_level_menu_item_when_installed_repeatedly(): void
    {
        $installer = app(FrontendMenuInstaller::class);

        $this->assertSame('created', $installer->install('Dự án', '/du-an', 'du-an'));
        $this->assertSame('unchanged', $installer->install('Dự án', '/du-an', 'du-an'));

        $this->assertSame(1, MenuItem::query()->where('url', '/du-an')->count());
        $this->assertDatabaseHas('menu_items', [
            'parent_id' => null,
            'label' => 'Dự án',
            'url' => '/du-an',
            'is_active' => true,
        ]);
    }

    public function test_it_repairs_an_existing_inactive_menu_item(): void
    {
        $item = MenuItem::create([
            'label' => 'Tên cũ',
            'type' => 'custom',
            'url' => '/dat-lich',
            'target' => '_blank',
            'sort_order' => 3,
            'is_active' => false,
        ]);

        $status = app(FrontendMenuInstaller::class)
            ->install('Đặt lịch', '/dat-lich', 'dat-lich');

        $this->assertSame('updated', $status);
        $this->assertSame(1, MenuItem::query()->where('url', '/dat-lich')->count());
        $this->assertDatabaseHas('menu_items', [
            'id' => $item->id,
            'label' => 'Đặt lịch',
            'target' => '_self',
            'is_active' => true,
        ]);
    }

    public function test_it_hides_matching_menu_items_and_can_restore_them(): void
    {
        $installer = app(FrontendMenuInstaller::class);
        $installer->install('Dự án', '/du-an', 'du-an');

        $this->assertSame('removed', $installer->remove('/du-an', 'du-an'));
        $this->assertSame('unchanged', $installer->remove('/du-an', 'du-an'));
        $this->assertDatabaseHas('menu_items', [
            'url' => '/du-an',
            'is_active' => false,
        ]);

        $this->assertSame('updated', $installer->install('Dự án', '/du-an', 'du-an'));
        $this->assertDatabaseHas('menu_items', [
            'url' => '/du-an',
            'is_active' => true,
        ]);
    }
}
