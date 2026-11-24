<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'image', 'sort_order', 'is_active', 'parent_id'];

    protected $casts = ['is_active' => 'boolean'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function isParent(): bool
    {
        return is_null($this->parent_id);
    }

    public function getImageUrlAttribute(): string
    {
        if (! $this->image) {
            return '';
        }
        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        return asset('storage/'.$this->image);
    }
}
