<?php

namespace App\Services\Theme;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class ThemeAssetVersion
{
    private const SETTING_PREFIX = 'theme_asset_version_';

    public function current(string $theme): string
    {
        if (! Schema::hasTable('settings')) {
            return '1';
        }

        return (string) (Setting::get($this->key($theme), '1') ?: '1');
    }

    public function bump(string $theme): string
    {
        $version = now()->format('YmdHis').'-'.bin2hex(random_bytes(4));
        if (! Schema::hasTable('settings')) {
            return $version;
        }

        Setting::set($this->key($theme), $version);

        return $version;
    }

    public function forget(string $theme): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        Setting::where('setting_key', $this->key($theme))->delete();
        Setting::clearCache();
    }

    private function key(string $theme): string
    {
        return self::SETTING_PREFIX.$theme;
    }
}
