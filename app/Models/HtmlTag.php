<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HtmlTag extends Model
{
    protected $fillable = ['name', 'position', 'description', 'content', 'is_active', 'only_homepage', 'sort_order'];

    protected $casts = [
        'is_active' => 'boolean',
        'only_homepage' => 'boolean',
    ];

    public static function renderPosition(string $position): string
    {
        $isHome = request()->routeIs('home');

        return static::where('position', $position)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->filter(fn ($tag) => ! $tag->only_homepage || $isHome)
            ->pluck('content')
            ->implode("\n");
    }
}
