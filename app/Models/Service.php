<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'short_desc', 'description',
        'price', 'duration', 'image', 'is_featured', 'is_active', 'views',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getImageUrlAttribute(): string
    {
        if (! $this->image) {
            return '';
        }
        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }
        if (str_starts_with($this->image, 'theme-assets/')) {
            return asset($this->image);
        }

        return asset('storage/'.$this->image);
    }
}
