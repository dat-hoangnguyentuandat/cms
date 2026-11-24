<?php

namespace App\Services;

use App\Models\LocalBusiness;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class LocalBusinessService
{
    /**
     * Import from JSON-LD structured data
     */
    public function importFromJsonLd(array $jsonLd, bool $setAsPrimary = false): LocalBusiness
    {
        // Extract basic info
        $name = $this->cleanText($jsonLd['name'] ?? '');
        if (! $name) {
            throw new \InvalidArgumentException('Business name is required');
        }

        // Find existing by name or create new
        $business = LocalBusiness::where('name', $name)->first();

        $data = [
            'name' => $name,
            'description' => $this->cleanText($jsonLd['description'] ?? '') ?: null,
            'url' => $this->cleanUrl($jsonLd['url'] ?? '') ?: null,
            'logo_url' => $this->cleanUrl($jsonLd['logo'] ?? '') ?: null,
            'image_url' => $this->cleanUrl($jsonLd['image'] ?? '') ?: null,
            'telephone' => $this->cleanText($jsonLd['telephone'] ?? '') ?: null,
            'email' => $this->cleanText($jsonLd['email'] ?? '') ?: null,
            'address' => $this->parseJsonLdAddress($jsonLd),
            'geo_coordinates' => $this->parseJsonLdGeo($jsonLd),
            'google_place_id' => null,
            'google_map_url' => $this->cleanUrl($jsonLd['hasMap'] ?? '') ?: null,
            'price_range' => $this->cleanText($jsonLd['priceRange'] ?? '') ?: null,
            'payment_accepted' => $this->parseJsonLdPayment($jsonLd),
            'currencies_accepted' => $this->parseJsonLdCurrencies($jsonLd),
            'opening_hours' => $this->parseJsonLdOpeningHours($jsonLd),
            'aggregate_rating' => $this->parseJsonLdRating($jsonLd),
            'review_count' => $this->parseJsonLdReviewCount($jsonLd),
            'social_profiles' => $this->parseJsonLdSameAs($jsonLd),
            'schema_type' => $this->cleanText($jsonLd['@type'] ?? 'LocalBusiness'),
            'metadata' => [
                'source' => 'json-ld',
                'imported_at' => now()->toIso8601String(),
                'raw_json_ld' => $jsonLd,
            ],
            'is_active' => true,
        ];

        if ($setAsPrimary) {
            LocalBusiness::where('is_primary', true)->update(['is_primary' => false]);
            $data['is_primary'] = true;
        }

        if ($business) {
            $business->update($data);
        } else {
            $business = LocalBusiness::create($data);
        }

        return $business;
    }

    /**
     * Parse Google Places data and create/update LocalBusiness
     */
    public function importFromGooglePlaces(array $payload, bool $setAsPrimary = false): LocalBusiness
    {
        $place = $payload['place'] ?? [];
        $summary = $payload['summary'] ?? [];

        // Extract basic info
        $name = $this->cleanText($place['name'] ?? $summary['name'] ?? '');
        if (! $name) {
            throw new \InvalidArgumentException('Place name is required');
        }

        $googlePlaceId = $this->cleanText($place['id'] ?? $place['place_id'] ?? '');
        $googleMapUrl = $this->cleanUrl($place['google_map_url'] ?? $summary['google_map_url'] ?? '');

        // Find existing or create new
        $business = null;
        if ($googlePlaceId) {
            $business = LocalBusiness::where('google_place_id', $googlePlaceId)->first();
        }

        if (! $business && $googleMapUrl) {
            $business = LocalBusiness::where('google_map_url', $googleMapUrl)->first();
        }

        $data = [
            'name' => $name,
            'description' => $this->extractDescription($place, $summary),
            'url' => $this->extractWebsiteUrl($place, $summary),
            'logo_url' => null, // Manual input
            'image_url' => $this->cleanUrl($place['thumbnail'] ?? $summary['thumbnail'] ?? ''),
            'telephone' => $this->extractPhone($place, $summary),
            'email' => null, // Manual input
            'address' => $this->extractAddress($place, $summary),
            'geo_coordinates' => $this->extractGeoCoordinates($place, $summary),
            'google_place_id' => $googlePlaceId,
            'google_map_url' => $googleMapUrl,
            'price_range' => $this->extractPriceRange($place, $summary),
            'payment_accepted' => null, // Manual input
            'currencies_accepted' => null, // Manual input
            'opening_hours' => $this->extractOpeningHours($place, $summary),
            'aggregate_rating' => $this->extractRating($place, $summary),
            'review_count' => $this->extractReviewCount($place, $summary),
            'social_profiles' => null, // Manual input
            'schema_type' => $this->guessSchemaType($place, $summary),
            'metadata' => [
                'place' => $place,
                'summary' => $summary,
                'imported_at' => now()->toIso8601String(),
                'raw_payload' => $payload,
            ],
            'is_active' => true,
        ];

        if ($setAsPrimary) {
            // Unset other primary businesses
            LocalBusiness::where('is_primary', true)->update(['is_primary' => false]);
            $data['is_primary'] = true;
        }

        if ($business) {
            $business->update($data);
        } else {
            $business = LocalBusiness::create($data);
        }

        return $business;
    }

    private function extractDescription(array $place, array $summary): ?string
    {
        $desc = $this->cleanText($place['description'] ?? $summary['description'] ?? '');

        return $desc ?: null;
    }

    private function extractWebsiteUrl(array $place, array $summary): ?string
    {
        return $this->cleanUrl($place['website'] ?? $place['url'] ?? $summary['website'] ?? $summary['url'] ?? '');
    }

    private function extractPhone(array $place, array $summary): ?string
    {
        return $this->cleanText($place['phone'] ?? $place['telephone'] ?? $summary['phone'] ?? $summary['telephone'] ?? '');
    }

    private function extractAddress(array $place, array $summary): ?array
    {
        $address = $place['address'] ?? $summary['address'] ?? '';

        if (! $address) {
            return null;
        }

        // If address is already structured
        if (is_array($address)) {
            return [
                'street' => $this->cleanText($address['street'] ?? $address['street_address'] ?? ''),
                'city' => $this->cleanText($address['city'] ?? $address['locality'] ?? ''),
                'state' => $this->cleanText($address['state'] ?? $address['region'] ?? ''),
                'postal_code' => $this->cleanText($address['postal_code'] ?? $address['zip'] ?? ''),
                'country' => $this->cleanText($address['country'] ?? ''),
            ];
        }

        // If address is string, parse it (basic parsing)
        return [
            'street' => $this->cleanText($address),
            'city' => null,
            'state' => null,
            'postal_code' => null,
            'country' => null,
        ];
    }

    private function extractGeoCoordinates(array $place, array $summary): ?array
    {
        $lat = $place['latitude'] ?? $place['lat'] ?? $summary['latitude'] ?? $summary['lat'] ?? null;
        $lng = $place['longitude'] ?? $place['lng'] ?? $place['long'] ?? $summary['longitude'] ?? $summary['lng'] ?? $summary['long'] ?? null;

        if ($lat && $lng) {
            return [
                'latitude' => (float) $lat,
                'longitude' => (float) $lng,
            ];
        }

        // Check nested geo object
        $geo = $place['geo'] ?? $summary['geo'] ?? null;
        if (is_array($geo)) {
            $lat = $geo['latitude'] ?? $geo['lat'] ?? null;
            $lng = $geo['longitude'] ?? $geo['lng'] ?? $geo['long'] ?? null;

            if ($lat && $lng) {
                return [
                    'latitude' => (float) $lat,
                    'longitude' => (float) $lng,
                ];
            }
        }

        return null;
    }

    private function extractPriceRange(array $place, array $summary): ?string
    {
        $priceLevel = $place['price_level'] ?? $summary['price_level'] ?? null;

        if ($priceLevel !== null) {
            $level = (int) $priceLevel;

            return str_repeat('$', max(1, min(4, $level)));
        }

        return null;
    }

    private function extractOpeningHours(array $place, array $summary): ?array
    {
        $hours = $place['opening_hours'] ?? $place['hours'] ?? $summary['opening_hours'] ?? $summary['hours'] ?? null;

        if (! $hours) {
            return null;
        }

        if (is_array($hours)) {
            return $hours;
        }

        // If string, wrap in array
        return ['raw' => $this->cleanText($hours)];
    }

    private function extractRating(array $place, array $summary): ?float
    {
        $rating = $place['rating'] ?? $summary['rating'] ?? null;

        if ($rating !== null) {
            return round((float) $rating, 2);
        }

        return null;
    }

    private function extractReviewCount(array $place, array $summary): int
    {
        $count = $place['review_count'] ?? $place['reviews_count'] ?? $place['user_ratings_total'] ?? $summary['review_count'] ?? $summary['reviews_count'] ?? 0;

        return max(0, (int) $count);
    }

    private function guessSchemaType(array $place, array $summary): string
    {
        $types = $place['types'] ?? $place['categories'] ?? $summary['types'] ?? $summary['categories'] ?? [];

        if (! is_array($types)) {
            return 'LocalBusiness';
        }

        $typesLower = array_map('strtolower', $types);

        // Map Google types to Schema.org types
        $mapping = [
            'restaurant' => 'Restaurant',
            'food' => 'Restaurant',
            'cafe' => 'CafeOrCoffeeShop',
            'coffee' => 'CafeOrCoffeeShop',
            'store' => 'Store',
            'shop' => 'Store',
            'hotel' => 'Hotel',
            'lodging' => 'LodgingBusiness',
            'hospital' => 'Hospital',
            'medical' => 'MedicalClinic',
            'dentist' => 'Dentist',
            'lawyer' => 'Attorney',
            'attorney' => 'Attorney',
            'bank' => 'BankOrCreditUnion',
            'gym' => 'ExerciseGym',
            'fitness' => 'HealthAndBeautyBusiness',
            'salon' => 'BeautySalon',
            'spa' => 'DaySpa',
            'automotive' => 'AutomotiveBusiness',
            'repair' => 'ProfessionalService',
        ];

        foreach ($typesLower as $type) {
            foreach ($mapping as $keyword => $schemaType) {
                if (str_contains($type, $keyword)) {
                    return $schemaType;
                }
            }
        }

        return 'LocalBusiness';
    }

    private function cleanText(mixed $value): string
    {
        if (! is_scalar($value)) {
            return '';
        }

        return trim((string) $value);
    }

    private function cleanUrl(mixed $value): ?string
    {
        $url = $this->cleanText($value);
        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        return $url;
    }

    private function parseJsonLdAddress(array $jsonLd): ?array
    {
        $address = $jsonLd['address'] ?? null;

        if (! $address || ! is_array($address)) {
            return null;
        }

        return [
            'street' => $this->cleanText($address['streetAddress'] ?? '') ?: null,
            'city' => $this->cleanText($address['addressLocality'] ?? '') ?: null,
            'state' => $this->cleanText($address['addressRegion'] ?? '') ?: null,
            'postal_code' => $this->cleanText($address['postalCode'] ?? '') ?: null,
            'country' => $this->cleanText($address['addressCountry'] ?? '') ?: null,
        ];
    }

    private function parseJsonLdGeo(array $jsonLd): ?array
    {
        $geo = $jsonLd['geo'] ?? null;

        if (! $geo || ! is_array($geo)) {
            return null;
        }

        $lat = $geo['latitude'] ?? null;
        $lng = $geo['longitude'] ?? null;

        if ($lat && $lng) {
            return [
                'latitude' => (float) $lat,
                'longitude' => (float) $lng,
            ];
        }

        return null;
    }

    private function parseJsonLdPayment(array $jsonLd): ?array
    {
        $payment = $jsonLd['paymentAccepted'] ?? null;

        if (! $payment) {
            return null;
        }

        // If string like "Cash, Credit Card", split it
        if (is_string($payment)) {
            return array_map('trim', explode(',', $payment));
        }

        // If already array
        if (is_array($payment)) {
            return $payment;
        }

        return null;
    }

    private function parseJsonLdCurrencies(array $jsonLd): ?array
    {
        $currencies = $jsonLd['currenciesAccepted'] ?? null;

        if (! $currencies) {
            return null;
        }

        // If string like "USD, VND", split it
        if (is_string($currencies)) {
            return array_map('trim', explode(',', $currencies));
        }

        // If already array
        if (is_array($currencies)) {
            return $currencies;
        }

        return null;
    }

    private function parseJsonLdOpeningHours(array $jsonLd): ?array
    {
        $hours = $jsonLd['openingHours'] ?? null;

        if (! $hours) {
            return null;
        }

        // If already array
        if (is_array($hours)) {
            return $hours;
        }

        // If string, wrap it
        if (is_string($hours)) {
            return ['raw' => $hours];
        }

        return null;
    }

    private function parseJsonLdRating(array $jsonLd): ?float
    {
        $aggregateRating = $jsonLd['aggregateRating'] ?? null;

        if (! $aggregateRating || ! is_array($aggregateRating)) {
            return null;
        }

        $ratingValue = $aggregateRating['ratingValue'] ?? null;

        if ($ratingValue !== null) {
            return round((float) $ratingValue, 2);
        }

        return null;
    }

    private function parseJsonLdReviewCount(array $jsonLd): int
    {
        $aggregateRating = $jsonLd['aggregateRating'] ?? null;

        if (! $aggregateRating || ! is_array($aggregateRating)) {
            return 0;
        }

        $count = $aggregateRating['ratingCount'] ?? $aggregateRating['reviewCount'] ?? 0;

        return max(0, (int) $count);
    }

    private function parseJsonLdSameAs(array $jsonLd): ?array
    {
        $sameAs = $jsonLd['sameAs'] ?? null;

        if (! $sameAs) {
            return null;
        }

        // If array of URLs
        if (is_array($sameAs)) {
            $profiles = [];
            foreach ($sameAs as $url) {
                $url = $this->cleanUrl($url);
                if ($url) {
                    // Try to guess the platform from URL
                    if (str_contains($url, 'facebook.com')) {
                        $profiles['facebook'] = $url;
                    } elseif (str_contains($url, 'instagram.com')) {
                        $profiles['instagram'] = $url;
                    } elseif (str_contains($url, 'twitter.com') || str_contains($url, 'x.com')) {
                        $profiles['twitter'] = $url;
                    } elseif (str_contains($url, 'linkedin.com')) {
                        $profiles['linkedin'] = $url;
                    } elseif (str_contains($url, 'youtube.com')) {
                        $profiles['youtube'] = $url;
                    } else {
                        $profiles[] = $url;
                    }
                }
            }

            return $profiles ?: null;
        }

        // If single URL string
        if (is_string($sameAs) && $url = $this->cleanUrl($sameAs)) {
            return [$url];
        }

        return null;
    }
}
