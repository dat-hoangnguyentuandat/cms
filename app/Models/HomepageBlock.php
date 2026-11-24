<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageBlock extends Model
{
    protected $fillable = [
        'theme_name',
        'zone',
        'block_type',
        'style_key',
        'title',
        'settings',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function displayTitle(): string
    {
        return $this->title ?: $this->block_type;
    }
}
