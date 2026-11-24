<?php

namespace Tests\Unit;

use App\Services\Widget\WidgetRegistry;
use App\Services\Widget\WidgetSettingsSchemaValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class WidgetSettingsSchemaValidatorTest extends TestCase
{
    private string $themesPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->themesPath = storage_path('app/test-widget-schema-themes');
        File::ensureDirectoryExists($this->themesPath.'/default');
        File::put($this->themesPath.'/default/widgets.php', <<<'PHP'
<?php

return [
    'widgets' => [
        'menu' => [
            'label' => 'Menu',
            'allowed_zones' => ['footer_1'],
            'settings_schema' => [
                'items' => [
                    'type' => 'repeater',
                    'fields' => [
                        'label' => ['type' => 'text', 'max' => 120],
                        'url' => ['type' => 'url', 'max' => 255],
                    ],
                ],
            ],
        ],
    ],
];
PHP);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->themesPath);

        parent::tearDown();
    }

    public function test_it_validates_and_collects_repeater_schema_settings(): void
    {
        $registry = new WidgetRegistry($this->themesPath);
        $registry->load('default');
        $validator = new WidgetSettingsSchemaValidator($registry);

        $request = Request::create('/admin/bo-cuc-trang-chu', 'POST', [
            'settings' => [
                'items' => [
                    ['label' => 'Trang chủ', 'url' => '/'],
                    ['label' => '', 'url' => ''],
                ],
            ],
        ]);

        $this->assertTrue($validator->hasSchema('menu'));
        $this->assertSame([
            'items' => [
                ['label' => 'Trang chủ', 'url' => '/'],
            ],
        ], $validator->validate($request, 'menu'));
    }
}
