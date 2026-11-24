<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PluginSetting extends Model
{
    protected $fillable = ['plugin_name', 'setting_key', 'setting_value', 'setting_group'];

    public static function get(string $pluginName, string $key, $default = null)
    {
        return static::where('plugin_name', $pluginName)
            ->where('setting_key', $key)
            ->value('setting_value') ?? $default;
    }

    public static function set(string $pluginName, string $key, $value, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['plugin_name' => $pluginName, 'setting_key' => $key],
            ['setting_value' => $value, 'setting_group' => $group],
        );
    }
}
