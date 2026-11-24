<?php

use App\Services\Widget\WidgetRegistry;
use App\Services\Plugin\HookManager;
use App\Services\Plugin\ShortcodeManager;
use App\Services\Theme\ThemeAssetVersion;

if (! function_exists('theme_asset')) {
    /**
     * Resolve a path inside the active theme's assets folder to a public URL.
     * Falls back to /theme-assets/default/{path} when no theme is bound.
     */
    function theme_asset(string $path): string
    {
        $name = app()->bound('theme.name') ? app('theme.name') : 'default';
        $path = ltrim($path, '/');

        return asset('theme-assets/'.$name.'/'.$path);
    }
}

if (! function_exists('theme_asset_version')) {
    function theme_asset_version(?string $theme = null): string
    {
        $name = $theme ?: active_theme();

        return app(ThemeAssetVersion::class)->current($name);
    }
}

if (! function_exists('versioned_theme_asset')) {
    function versioned_theme_asset(string $path): string
    {
        $url = theme_asset($path);
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.'v='.rawurlencode(theme_asset_version());
    }
}

if (! function_exists('active_theme')) {
    function active_theme(): string
    {
        return app()->bound('theme.name') ? app('theme.name') : 'default';
    }
}

if (! function_exists('widget_view')) {
    /**
     * Resolve the blade view for a widget type in a given zone.
     * Returns null if the widget is not supported in that zone.
     */
    function widget_view(string $type, string $zone): ?string
    {
        return app(WidgetRegistry::class)->view($type, $zone);
    }
}

if (! function_exists('cms_apply_hook')) {
    function cms_apply_hook(string $hook, mixed $value, mixed ...$args): mixed
    {
        return app(HookManager::class)->apply($hook, $value, ...$args);
    }
}

if (! function_exists('cms_do_hook')) {
    function cms_do_hook(string $hook, mixed ...$args): void
    {
        app(HookManager::class)->do($hook, ...$args);
    }
}

if (! function_exists('cms_shortcodes')) {
    function cms_shortcodes(string $content): string
    {
        return app(ShortcodeManager::class)->render($content);
    }
}

if (! function_exists('cms_content')) {
    function cms_content(?string $content, mixed ...$args): string
    {
        $rendered = cms_shortcodes((string) $content);

        return (string) cms_apply_hook('cms.content.rendered', $rendered, ...$args);
    }
}
