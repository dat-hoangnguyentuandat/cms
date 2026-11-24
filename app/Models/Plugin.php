<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    protected $fillable = [
        'name',
        'title',
        'version',
        'author',
        'description',
        'is_active',
        'path',
        'manifest',
        'installed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'manifest' => 'array',
        'installed_at' => 'datetime',
    ];
}
