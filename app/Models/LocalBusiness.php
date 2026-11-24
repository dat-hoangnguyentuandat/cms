<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocalBusiness extends Model
{
    protected $fillable = [
        'name',
        'description',
        'url',
        'logo_url',
        'image_url',
        'telephone',
        'email',
        'address',
        'geo_coordinates',
        'google_place_id',
        'google_map_url',
        'price_range',
        'payment_accepted',
        'currencies_accepted',
        'opening_hours',
        'aggregate_rating',
        'review_count',
        'social_profiles',
        'schema_type',
        'metadata',
        'is_active',
        'is_primary',
    ];

    protected $casts = [
        'address' => 'array',
        'geo_coordinates' => 'array',
        'payment_accepted' => 'array',
        'currencies_accepted' => 'array',
        'opening_hours' => 'array',
        'social_profiles' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'is_primary' => 'boolean',
        'aggregate_rating' => 'decimal:2',
        'review_count' => 'integer',
    ];

    public static function primary(): ?self
    {
        return static::where('is_active', true)
            ->where('is_primary', true)
            ->first();
    }

    public static function active()
    {
        return static::where('is_active', true)
            ->orderBy('is_primary', 'desc')
            ->orderBy('id');
    }

    public function getLatitudeAttribute(): ?float
    {
        return $this->geo_coordinates['latitude'] ?? null;
    }

    public function getLongitudeAttribute(): ?float
    {
        return $this->geo_coordinates['longitude'] ?? null;
    }

    public function getFullAddressAttribute(): ?string
    {
        if (! $this->address) {
            return null;
        }

        $parts = array_filter([
            $this->address['street'] ?? null,
            $this->address['city'] ?? null,
            $this->address['state'] ?? null,
            $this->address['postal_code'] ?? null,
            $this->address['country'] ?? null,
        ]);

        return $parts ? implode(', ', $parts) : null;
    }

    public function syncAggregateRatingFromTestimonials(): void
    {
        if (! $this->google_place_id) {
            return;
        }

        $stats = Testimonial::where('place_id', $this->google_place_id)
            ->where('is_active', true)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as count')
            ->first();

        if ($stats && $stats->count > 0) {
            $this->update([
                'aggregate_rating' => round($stats->avg_rating, 2),
                'review_count' => $stats->count,
            ]);
        }
    }
}
