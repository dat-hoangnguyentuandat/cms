<?php

namespace App\Http\Middleware;

use App\Models\Plugin;
use App\Models\PluginSetting;
use App\Services\HtmlPlacementInjector;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class InjectPluginUi
{
    public function __construct(private HtmlPlacementInjector $injector) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->attributes->get('platform_runtime')) {
            return $response;
        }

        if (! $this->supportsInjection($request, $response)) {
            return $response;
        }

        $active = Plugin::query()
            ->whereIn('name', ['portfolio', 'booking'])
            ->where('is_active', true)
            ->pluck('name')
            ->all();
        $links = [];

        if (in_array('portfolio', $active, true)
            && PluginSetting::get('portfolio', 'inject_header_link', '0') === '1') {
            $links[] = ['plugin' => 'portfolio', 'label' => PluginSetting::get('portfolio', 'menu_label', 'Dự án'), 'url' => '/du-an'];
        }
        if (in_array('booking', $active, true)
            && PluginSetting::get('booking', 'inject_header_link', '0') === '1') {
            $links[] = ['plugin' => 'booking', 'label' => PluginSetting::get('booking', 'menu_label', 'Đặt lịch'), 'url' => '/dat-lich'];
        }

        $html = (string) $response->getContent();
        foreach ($links as $index => $link) {
            if ($this->headerContainsUrl($html, $link['url'])) {
                unset($links[$index]);
            }
        }
        if ($links === []) {
            return $response;
        }
        $links = array_values($links);

        $anchors = collect($links)->map(function (array $link, int $index): string {
            $marker = $index === 0 ? ' data-cms-injection="plugin-header-links"' : '';

            return '<a'.$marker.' class="cms-plugin-header-link cms-plugin-header-link--'.e($link['plugin']).'" href="'.e(url($link['url'])).'">'.e((string) $link['label']).'</a>';
        })->implode('');
        $fallback = '<div data-cms-injection="plugin-header-links" class="cms-plugin-header-links-fallback">'.$anchors.'</div><style>
.cms-plugin-header-links-fallback{position:fixed;top:12px;right:12px;z-index:2147483000;display:flex;gap:8px;align-items:center}
.cms-plugin-header-links-fallback .cms-plugin-header-link{display:inline-flex;align-items:center;min-height:38px;padding:8px 12px;border-radius:7px;background:#111827;color:#fff!important;font:600 14px/1.2 system-ui,sans-serif;text-decoration:none;box-shadow:0 4px 14px #0003}
</style>';

        $headerHasNavigation = preg_match('/<header\b[^>]*>.*?<nav\b[^>]*>.*?<\/nav>.*?<\/header>/is', $html) === 1;
        $injected = $headerHasNavigation ? null : $this->injector->injectBeforeMarkerInHeader(
            $html,
            $anchors,
            'theme-i18n-switcher-header',
        );
        $response->setContent($injected ?? $this->injector->inject(
            $html,
            $anchors,
            'header',
            'plugin-header-links',
            $fallback,
        ));

        return $response;
    }

    private function supportsInjection(Request $request, Response $response): bool
    {
        return ! $request->is('admin*')
            && ! $request->expectsJson()
            && $response instanceof \Illuminate\Http\Response
            && str_contains($response->headers->get('Content-Type', ''), 'text/html')
            && Schema::hasTable('plugins')
            && Schema::hasTable('plugin_settings');
    }

    private function headerContainsUrl(string $html, string $path): bool
    {
        if (! preg_match('/<header\b[^>]*>.*?<\/header>/is', $html, $header)) {
            return false;
        }

        return preg_match('/\bhref\s*=\s*(["\'])[^"\']*'.preg_quote($path, '/').'(?:[?#][^"\']*)?\1/i', $header[0]) === 1;
    }
}
