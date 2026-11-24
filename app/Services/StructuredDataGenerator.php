<?php

namespace App\Services;

use App\Models\LocalBusiness;
use Illuminate\Support\Arr;

class StructuredDataGenerator
{
    /**
     * Generate JSON-LD structured data for LocalBusiness
     */
    public function generateLocalBusiness(LocalBusiness $business): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => $business->schema_type ?: 'LocalBusiness',
            'name' => $business->name,
        ];

        // Description
        if ($business->description) {
            $data['description'] = $business->description;
        }

        // URL
        if ($business->url) {
            $data['url'] = $business->url;
        }

        // Telephone
        if ($business->telephone) {
            $data['telephone'] = $business->telephone;
        }

        // Email
        if ($business->email) {
            $data['email'] = $business->email;
        }

        // Logo
        if ($business->logo_url) {
            $data['logo'] = $business->logo_url;
        }

        // Image
        if ($business->image_url) {
            $data['image'] = $business->image_url;
        }

        // Address
        if ($business->address) {
            $address = [
                '@type' => 'PostalAddress',
            ];

            if ($street = $business->address['street'] ?? null) {
                $address['streetAddress'] = $street;
            }

            if ($city = $business->address['city'] ?? null) {
                $address['addressLocality'] = $city;
            }

            if ($state = $business->address['state'] ?? null) {
                $address['addressRegion'] = $state;
            }

            if ($postalCode = $business->address['postal_code'] ?? null) {
                $address['postalCode'] = $postalCode;
            }

            if ($country = $business->address['country'] ?? null) {
                $address['addressCountry'] = $country;
            }

            if (count($address) > 1) { // More than just @type
                $data['address'] = $address;
            }
        }

        // Geo Coordinates
        if ($business->geo_coordinates) {
            $lat = $business->geo_coordinates['latitude'] ?? null;
            $lng = $business->geo_coordinates['longitude'] ?? null;

            if ($lat && $lng) {
                $data['geo'] = [
                    '@type' => 'GeoCoordinates',
                    'latitude' => $lat,
                    'longitude' => $lng,
                ];
            }
        }

        // hasMap (Google Maps URL)
        if ($business->google_map_url) {
            $data['hasMap'] = $business->google_map_url;
        }

        // Price Range
        if ($business->price_range) {
            $data['priceRange'] = $business->price_range;
        }

        // Payment Accepted
        if ($business->payment_accepted && is_array($business->payment_accepted)) {
            $data['paymentAccepted'] = implode(', ', $business->payment_accepted);
        }

        // Currencies Accepted
        if ($business->currencies_accepted && is_array($business->currencies_accepted)) {
            $data['currenciesAccepted'] = implode(', ', $business->currencies_accepted);
        }

        // Opening Hours
        if ($business->opening_hours) {
            $openingHours = $this->formatOpeningHours($business->opening_hours);
            if ($openingHours) {
                $data['openingHours'] = $openingHours;
            }
        }

        // Aggregate Rating
        if ($business->aggregate_rating && $business->review_count > 0) {
            $data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => (string) $business->aggregate_rating,
                'bestRating' => '5',
                'worstRating' => '1',
                'ratingCount' => (string) $business->review_count,
            ];
        }

        // Social Profiles (sameAs)
        if ($business->social_profiles && is_array($business->social_profiles)) {
            $sameAs = array_values(array_filter($business->social_profiles, function ($url) {
                return filter_var($url, FILTER_VALIDATE_URL);
            }));

            if ($sameAs) {
                $data['sameAs'] = $sameAs;
            }
        }

        return $data;
    }

    /**
     * Generate JSON-LD script tag
     */
    public function generateScriptTag(LocalBusiness $business): string
    {
        $jsonLd = $this->generateLocalBusiness($business);

        return '<script type="application/ld+json">' . json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>';
    }

    /**
     * Format opening hours for Schema.org
     */
    private function formatOpeningHours(array $hours): array|string|null
    {
        // If already formatted as string (e.g., "Mo-Fr 09:00-17:00")
        if (isset($hours['raw']) && is_string($hours['raw'])) {
            return $hours['raw'];
        }

        // If structured format
        $formatted = [];
        $dayMap = [
            'monday' => 'Mo',
            'tuesday' => 'Tu',
            'wednesday' => 'We',
            'thursday' => 'Th',
            'friday' => 'Fr',
            'saturday' => 'Sa',
            'sunday' => 'Su',
        ];

        foreach ($dayMap as $day => $abbr) {
            if (isset($hours[$day])) {
                $dayHours = $hours[$day];
                if (is_array($dayHours)) {
                    $open = $dayHours['open'] ?? null;
                    $close = $dayHours['close'] ?? null;
                    if ($open && $close) {
                        $formatted[] = "$abbr $open-$close";
                    }
                } elseif (is_string($dayHours) && $dayHours !== 'closed') {
                    $formatted[] = "$abbr $dayHours";
                }
            }
        }

        return $formatted ?: null;
    }

    /**
     * Get primary business structured data
     */
    public function getPrimaryBusinessScriptTag(): ?string
    {
        $business = LocalBusiness::primary();

        if (! $business) {
            return null;
        }

        return $this->generateScriptTag($business);
    }
}
