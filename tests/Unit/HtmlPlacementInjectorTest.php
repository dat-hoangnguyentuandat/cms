<?php

namespace Tests\Unit;

use App\Services\HtmlPlacementInjector;
use PHPUnit\Framework\TestCase;

class HtmlPlacementInjectorTest extends TestCase
{
    private HtmlPlacementInjector $injector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->injector = new HtmlPlacementInjector;
    }

    public function test_it_injects_into_semantic_header_navigation(): void
    {
        $html = '<html><body><header><nav><a href="/">Home</a></nav></header></body></html>';
        $markup = '<a data-cms-injection="booking" href="/dat-lich">Booking</a>';

        $result = $this->injector->inject($html, $markup, 'header', 'booking');

        $this->assertStringContainsString('Booking</a></nav>', $result);
    }

    public function test_it_uses_fixed_fallback_for_non_semantic_theme_markup(): void
    {
        $html = '<html><body><main>Content</main></body></html>';
        $markup = '<div data-cms-injection="languages">Languages</div>';
        $fallback = '<div data-cms-injection="languages" class="fixed">Languages</div>';

        $result = $this->injector->inject($html, $markup, 'header', 'languages', $fallback);

        $this->assertStringContainsString('<body>'.$fallback, $result);
    }

    public function test_it_does_not_inject_the_same_marker_twice(): void
    {
        $html = '<html><body><header><span data-cms-injection="languages">Existing</span></header></body></html>';

        $result = $this->injector->inject(
            $html,
            '<span data-cms-injection="languages">Duplicate</span>',
            'header',
            'languages',
        );

        $this->assertSame($html, $result);
    }

    public function test_it_can_keep_language_switcher_after_all_other_header_links(): void
    {
        $html = '<html><body><header><nav><a href="/">Home</a><div data-cms-injection="theme-i18n-switcher-header">VI</div></nav></header></body></html>';

        $result = $this->injector->injectBeforeMarkerInHeader(
            $html,
            '<a data-cms-injection="plugin-header-links" href="/dat-lich">Booking</a>',
            'theme-i18n-switcher-header',
        );

        $this->assertNotNull($result);
        $this->assertStringContainsString('Booking</a><div data-cms-injection="theme-i18n-switcher-header"', $result);
    }

    public function test_it_can_place_language_switcher_after_the_navigation_element(): void
    {
        $html = '<html><body><header><nav><a href="/">Home</a></nav><div class="actions">CTA</div></header></body></html>';
        $switcher = '<div data-cms-injection="theme-i18n-switcher-header">VI</div>';

        $result = $this->injector->injectAfterNavigationInHeader(
            $html,
            $switcher,
            'theme-i18n-switcher-header',
        );

        $this->assertNotNull($result);
        $this->assertStringContainsString('</nav>'.$switcher.'<div class="actions">', $result);
    }

    public function test_it_prefers_header_actions_for_an_isolated_language_switcher(): void
    {
        $html = '<html><body><header><nav>Menu</nav><div class="header-actions"><a href="tel:1">Call</a></div></header></body></html>';
        $switcher = '<div data-cms-injection="theme-i18n-switcher-header">VI</div>';

        $result = $this->injector->injectIntoHeaderActions(
            $html,
            $switcher,
            'theme-i18n-switcher-header',
        );

        $this->assertNotNull($result);
        $this->assertStringContainsString('<div class="header-actions">'.$switcher.'<a href="tel:1">', $result);
    }
}
