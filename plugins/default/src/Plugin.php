<?php

namespace Plugins\Default;

use App\Services\Plugin\HookManager;
use App\Services\Plugin\ShortcodeManager;
use Illuminate\Contracts\Foundation\Application;

class Plugin
{
    public function boot(Application $app): void
    {
        $app->make(ShortcodeManager::class)->register('default_notice', function (array $attrs): string {
            $text = $attrs['text'] ?? 'Thông báo từ Plugin Mặc định.';

            return '<div class="default-plugin-shortcode">'.e($text).'</div>';
        });

        $app->make(HookManager::class)->listen('cms.widget.rendered', function (string $html) {
            return $html;
        });
    }
}
