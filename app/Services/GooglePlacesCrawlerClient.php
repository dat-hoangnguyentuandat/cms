<?php

namespace App\Services;

use App\Exceptions\GoogleReviewImportException;
use Illuminate\Support\Facades\Http;

class GooglePlacesCrawlerClient
{
    public function detail(string $detailUrl, ?string $apiKey = null): array
    {
        $apiKey = $apiKey ?: config('services.google_crawler.api_key');

        $request = Http::acceptJson()->timeout(120);
        if ($apiKey) {
            $request = $request->withHeaders(['X-API-Key' => $apiKey]);
        }

        $response = $request->get($detailUrl);

        if (! $response->successful()) {
            throw new GoogleReviewImportException('API detail thất bại (HTTP '.$response->status().'): '.substr($response->body(), 0, 500));
        }

        return $this->parseResponse($response->body());
    }

    public function crawl(string $googleMapUrl, int $max, ?string $apiKey = null): array
    {
        $endpoint = config('services.google_crawler.url', 'https://api-map.19t.vn/api/v1/google/crawl-places-stream');
        $apiKey = $apiKey ?: config('services.google_crawler.api_key');

        if (! $apiKey) {
            throw new GoogleReviewImportException('Thiếu API key để gọi Google crawler.');
        }

        $response = Http::withHeaders([
            'X-API-Key' => $apiKey,
            'Accept' => 'text/event-stream',
        ])->timeout(600)->post($endpoint, [
            'googleMapUrl' => $googleMapUrl,
            'max' => $max,
        ]);

        if (! $response->successful()) {
            throw new GoogleReviewImportException('API crawl thất bại (HTTP '.$response->status().'): '.substr($response->body(), 0, 500));
        }

        return $this->parseResponse($response->body());
    }

    private function parseResponse(string $body): array
    {
        $direct = json_decode($body, true);
        if (is_array($direct)) {
            return $this->resolvePayload($direct);
        }

        $events = $this->parseSseEvents($body);
        if (! $events) {
            throw new GoogleReviewImportException('Không đọc được dữ liệu từ API crawl.');
        }

        $lastError = null;
        $lastWithPayload = null;
        foreach ($events as $event) {
            $type = $event['type'] ?? '';
            $data = $event['data'] ?? null;

            if ($type === 'error' && is_array($data)) {
                $lastError = $data['message'] ?? 'API crawl báo lỗi không rõ.';

                continue;
            }

            if (is_array($data) && $this->payloadHasContent($data)) {
                $lastWithPayload = $data;
            }
        }

        if ($lastWithPayload) {
            return $this->resolvePayload($lastWithPayload);
        }

        if ($lastError) {
            throw new GoogleReviewImportException('API crawl báo lỗi: '.$lastError);
        }

        throw new GoogleReviewImportException('API crawl không trả về dữ liệu place/reviews.');
    }

    private function parseSseEvents(string $body): array
    {
        $events = [];
        $currentEvent = ['type' => 'message', 'data_lines' => []];

        foreach (preg_split('/\r\n|\r|\n/', $body) as $line) {
            if ($line === '') {
                if ($currentEvent['data_lines']) {
                    $events[] = $this->finalizeSseEvent($currentEvent);
                }
                $currentEvent = ['type' => 'message', 'data_lines' => []];

                continue;
            }

            if (str_starts_with($line, ':')) {
                continue;
            }

            if (str_starts_with($line, 'event:')) {
                $currentEvent['type'] = trim(substr($line, 6));

                continue;
            }

            if (str_starts_with($line, 'data:')) {
                $currentEvent['data_lines'][] = ltrim(substr($line, 5));

                continue;
            }
        }

        if ($currentEvent['data_lines']) {
            $events[] = $this->finalizeSseEvent($currentEvent);
        }

        return $events;
    }

    private function finalizeSseEvent(array $event): array
    {
        $raw = implode("\n", $event['data_lines']);
        $decoded = json_decode($raw, true);

        return [
            'type' => $event['type'],
            'data' => is_array($decoded) ? $decoded : $raw,
        ];
    }

    private function payloadHasContent(array $payload): bool
    {
        if ($this->resolvePayloadCandidate($payload)) {
            return true;
        }

        return false;
    }

    private function resolvePayload(array $payload): array
    {
        $candidate = $this->resolvePayloadCandidate($payload);

        return $candidate ?: $payload;
    }

    private function resolvePayloadCandidate(array $payload): ?array
    {
        foreach (['data', 'payload', 'result', 'place'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                $candidate = $payload[$key];
                if ($this->looksLikePlacePayload($candidate)) {
                    return $candidate;
                }
            }
        }

        if ($this->looksLikePlacePayload($payload)) {
            return $payload;
        }

        if (isset($payload['places']) && is_array($payload['places']) && $payload['places']) {
            $first = $payload['places'][0] ?? null;
            if (is_array($first) && $this->looksLikePlacePayload($first)) {
                return $first;
            }
        }

        return null;
    }

    private function looksLikePlacePayload(array $payload): bool
    {
        return isset($payload['reviews']) || isset($payload['place']) || isset($payload['summary']);
    }
}
