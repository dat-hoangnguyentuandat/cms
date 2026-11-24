<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $fillable = ['category_id', 'title', 'slug', 'excerpt', 'content', 'image', 'author', 'is_published', 'views'];

    protected $casts = ['is_published' => 'boolean'];

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
