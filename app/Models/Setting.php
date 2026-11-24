<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $fillable = ['setting_key', 'setting_value', 'setting_group'];

    protected static array $cache = [];

    public static function get(string $key, $default = null)
    {
        if (empty(static::$cache)) {
            static::$cache = static::pluck('setting_value', 'setting_key')->toArray();
        }

        return static::$cache[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['setting_key' => $key], ['setting_value' => $value]);
        static::$cache[$key] = $value;
    }

    public static function clearCache(): void
    {
        static::$cache = [];
    }
}
