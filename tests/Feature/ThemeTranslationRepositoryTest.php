<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Plugins\ThemeI18n\Services\ThemeTranslationRepository;
use Tests\TestCase;

class ThemeTranslationRepositoryTest extends TestCase
{
    private string $translationsPath;

    protected function setUp(): void
    {
        parent::setUp();
        require_once base_path('plugins/theme-i18n/src/Services/ThemeTranslationRepository.php');

        $this->translationsPath = storage_path('app/test-theme-i18n');
        File::ensureDirectoryExists($this->translationsPath);
        File::put($this->translationsPath.'/default_en.xml', $this->xml('en', 'English', [
            'Trang chủ' => 'Home',
            'Đặt lịch' => 'Book now',
        ]));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->translationsPath);
        parent::tearDown();
    }

    public function test_new_theme_does_not_inherit_default_translation_pack(): void
    {
        $repository = new ThemeTranslationRepository($this->translationsPath);

        $this->assertSame([], $repository->languages('brand-new-theme'));
        $this->assertSame([], $repository->dictionary('brand-new-theme', 'en'));
    }

    public function test_theme_uses_only_its_specific_translation_pack(): void
    {
        File::put($this->translationsPath.'/custom_en.xml', $this->xml('en', 'Custom English', [
            'Trang chủ' => 'Custom home',
        ]));
        $repository = new ThemeTranslationRepository($this->translationsPath);

        $this->assertSame(['en' => 'Custom English'], $repository->languages('custom'));
        $this->assertSame(['Trang chủ' => 'Custom home'], $repository->dictionary('custom', 'en'));
    }

    private function xml(string $locale, string $label, array $items): string
    {
        $nodes = collect($items)->map(
            fn (string $target, string $source) => '<text key="'.e($source).'">'.e($target).'</text>'
        )->implode('');

        return '<?xml version="1.0" encoding="UTF-8"?><language code="'.$locale.'" label="'.$label.'">'.$nodes.'</language>';
    }
}
