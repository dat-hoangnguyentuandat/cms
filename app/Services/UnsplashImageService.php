<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UnsplashImageService
{
    private const API_BASE = 'https://api.unsplash.com';

    private string $accessKey;

    public function __construct()
    {
        // Use free Unsplash access key - 50 requests/hour
        $this->accessKey = config('services.unsplash.access_key', '');
    }

    /**
     * Crawl all spa images needed for theme
     */
    public function crawlSpaImages(): array
    {
        $downloaded = [];

        // 1. Hero banner
        $hero = $this->searchAndDownload(
            'asian woman spa beauty facial glowing skin',
            1,
            'hero-banner.jpg',
            'images'
        );
        if ($hero) {
            $downloaded['hero'] = $hero;
        }

        // 2. Service images
        $services = [
            'massage' => 'spa massage therapy treatment',
            'facial' => 'facial skincare beauty treatment',
            'body' => 'body scrub spa wellness',
            'sauna' => 'sauna steam room spa',
            'hair' => 'hair treatment salon spa',
            'manicure' => 'manicure nail spa',
        ];

        foreach ($services as $key => $query) {
            $img = $this->searchAndDownload($query, 1, "$key.jpg", 'images/services');
            if ($img) {
                $downloaded["service_$key"] = $img;
            }
        }

        // 3. Spa interior
        $interior = $this->searchAndDownload(
            'luxury spa room interior',
            1,
            'spa-interior.jpg',
            'images'
        );
        if ($interior) {
            $downloaded['spa_interior'] = $interior;
        }

        // 4. Promo images
        $promos = [
            'massage' => 'massage therapy spa',
            'facial' => 'facial treatment beauty',
            'body' => 'body treatment spa',
        ];

        foreach ($promos as $key => $query) {
            $img = $this->searchAndDownload($query, 1, "$key.jpg", 'images/promo');
            if ($img) {
                $downloaded["promo_$key"] = $img;
            }
        }

        // 5. Avatars
        $avatars = $this->searchAndDownload(
            'asian woman portrait professional',
            3,
            null,
            'images/avatars'
        );
        if ($avatars) {
            foreach ($avatars as $i => $avatar) {
                $downloaded['avatar_'.($i + 1)] = $avatar;
            }
        }

        return $downloaded;
    }

    private function searchImages(string $query, int $perPage = 10): ?array
    {
        if (empty($this->accessKey)) {
            Log::error('Unsplash access key not configured');

            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Client-ID '.$this->accessKey,
            ])->get(self::API_BASE.'/search/photos', [
                'query' => $query,
                'per_page' => $perPage,
                'orientation' => 'landscape',
            ]);

            if ($response->successful()) {
                return $response->json()['results'] ?? [];
            }

            Log::error('Unsplash API error: '.$response->status());
        } catch (\Exception $e) {
            Log::error('Unsplash exception: '.$e->getMessage());
        }

        return null;
    }

    private function downloadImage(string $url, string $path): bool
    {
        try {
            $contents = Http::get($url)->body();
            $fullPath = public_path("theme-assets/six-spa/$path");

            $dir = dirname($fullPath);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            file_put_contents($fullPath, $contents);

            return true;
        } catch (\Exception $e) {
            Log::error('Download failed: '.$e->getMessage());

            return false;
        }
    }

    private function searchAndDownload(string $query, int $count, ?string $filename, string $subPath): mixed
    {
        $results = $this->searchImages($query, $count);

        if (! $results) {
            return null;
        }

        if ($count === 1 && $filename) {
            $imageUrl = $results[0]['urls']['regular'] ?? null;
            if ($imageUrl && $this->downloadImage($imageUrl, "$subPath/$filename")) {
                return "theme-assets/six-spa/$subPath/$filename";
            }

            return null;
        }

        $downloaded = [];
        foreach (array_slice($results, 0, $count) as $i => $result) {
            $imageUrl = $result['urls']['regular'] ?? null;
            if ($imageUrl) {
                $name = 'avatar'.($i + 1).'.jpg';
                if ($this->downloadImage($imageUrl, "$subPath/$name")) {
                    $downloaded[] = "theme-assets/six-spa/$subPath/$name";
                }
            }
        }

        return $downloaded;
    }
}
