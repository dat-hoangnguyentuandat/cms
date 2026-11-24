<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'role',
        'content',
        'rating',
        'avatar',
        'is_active',
        'sort_order',
        'source',
        'source_id',
        'place_id',
        'place_name',
        'reviewer_name',
        'reviewer_avatar',
        'relative_date',
        'owner_response',
        'review_media',
        'source_url',
        'source_created_at',
        'place_data',
        'summary_data',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'review_media' => 'array',
        'place_data' => 'array',
        'summary_data' => 'array',
        'metadata' => 'array',
        'source_created_at' => 'datetime',
    ];

    public function getImageUrlAttribute(): string
    {
        if (! $this->avatar) {
            return '';
        }
        if (str_starts_with($this->avatar, 'http')) {
            return $this->avatar;
        }

        return asset('storage/'.$this->avatar);
    }

    public function getAvatarUrlAttribute(): string
    {
        $avatar = $this->reviewer_avatar ?: $this->avatar;

        if (! $avatar) {
            return '';
        }

        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            if (str_contains($avatar, 'googleusercontent.com')) {
                $avatar = preg_replace('/=w\d+-h\d+/', '=w240-h240', $avatar);
                if (! str_contains($avatar, '=w')) {
                    $avatar .= (str_contains($avatar, '=') ? '-w240-h240' : '=w240-h240');
                }
            }

            return $avatar;
        }

        if (str_starts_with($avatar, 'theme-assets/')) {
            return asset($avatar);
        }

        return asset('storage/'.$avatar);
    }
}
