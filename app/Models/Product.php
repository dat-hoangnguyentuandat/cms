<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'sku', 'brand', 'short_desc', 'description',
        'price', 'sale_price', 'stock', 'image', 'gallery', 'is_featured', 'is_active', 'views',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getDisplayPriceAttribute()
    {
        return $this->sale_price ?? $this->price;
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
