<?php

namespace App\Services;

use App\Exceptions\GoogleReviewImportException;
use App\Models\Testimonial;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GoogleReviewTestimonialImporter
{
    public function import(UploadedFile $file): array
    {
        $raw = file_get_contents($file->getRealPath());
        if ($raw === false || trim($raw) === '') {
            throw new GoogleReviewImportException('File JSON không có nội dung.');
        }

        return $this->importRaw($raw);
    }

    public function importRaw(string $raw): array
    {
        $payload = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($payload)) {
            throw new GoogleReviewImportException('Nội dung import không phải JSON hợp lệ.');
        }

        return $this->importPayload($payload);
    }

    public function importPayload(array $payload, ?int $maxReviews = null, bool $overwrite = false): array
    {
        $reviews = $this->extractReviews($payload);
        if (! $reviews) {
            throw new GoogleReviewImportException('Không tìm thấy danh sách reviews trong dữ liệu.');
        }

        if ($maxReviews !== null && $maxReviews > 0 && count($reviews) > $maxReviews) {
            $reviews = array_slice($reviews, 0, $maxReviews);
        }

        if ($overwrite) {
            Testimonial::where('source', 'google')->delete();
        }

        $place = $payload['place'] ?? [];
        $summary = $payload['summary'] ?? [];
        $media = $payload['media'] ?? [];
        $metadata = Arr::except($payload, ['reviews']);
        $metadata['top_level_media'] = $media;
        $metadata['crawl_status'] = $payload['crawl_status'] ?? null;
        $metadata['detail_crawled_at'] = $payload['detail_crawled_at'] ?? null;
        $metadata['updated_at'] = $payload['updated_at'] ?? null;
        $metadata['last_error'] = $payload['last_error'] ?? null;
        $metadata['cached'] = $payload['cached'] ?? null;
        $metadata['reviewPage'] = $payload['reviewPage'] ?? null;
        $metadata['metadata'] = $payload['metadata'] ?? null;
        $metadata['summary'] = $summary;
        $metadata['place'] = $place;
        $metadata['raw'] = $payload;
        $metadata['media_count'] = is_array($media) ? count($media) : 0;
        $metadata['review_count'] = is_array($reviews) ? count($reviews) : 0;
        $metadata['place_id'] = $place['id'] ?? null;
        $metadata['place_name'] = $place['name'] ?? null;
        $metadata['place_google_map_url'] = $place['google_map_url'] ?? null;
        $metadata['place_thumbnail'] = $place['thumbnail'] ?? null;
        $metadata['place_crawled_at'] = $place['crawled_at'] ?? null;
        $metadata['summary_google_map_url'] = $summary['google_map_url'] ?? null;
        $metadata['summary_thumbnail'] = $summary['thumbnail'] ?? null;
        $metadata['source_url'] = $place['google_map_url'] ?? $summary['google_map_url'] ?? null;
        $tableColumns = Schema::getColumnListing((new Testimonial)->getTable());
        $maxSort = (int) Testimonial::max('sort_order');
        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'invalid' => 0, 'total' => count($reviews)];
        $existingFingerprints = $this->loadExistingFingerprints($tableColumns);
        $supportsSourceColumns = in_array('source', $tableColumns, true) && in_array('source_id', $tableColumns, true);

        foreach ($reviews as $index => $review) {
            if (! is_array($review)) {
                $result['invalid']++;

                continue;
            }

            $data = $this->normalizeReview($review, $place, $summary, $metadata, $maxSort + $index + 1);
            if (! $data) {
                $result['invalid']++;

                continue;
            }

            $fingerprint = $this->fingerprint($data['source_id'], $data['name'], $data['content'], $data['rating']);
            if (isset($existingFingerprints[$fingerprint])) {
                $result['skipped']++;

                continue;
            }

            $lookup = Testimonial::query();
            if ($supportsSourceColumns) {
                $lookup->where('source', $data['source'])->where('source_id', $data['source_id']);
            } else {
                $lookup->where('name', $data['name'])->where('content', $data['content'])->where('rating', $data['rating']);
            }

            $testimonial = $lookup->first();
            if (! $testimonial && $supportsSourceColumns) {
                $testimonial = Testimonial::query()
                    ->where('name', $data['name'])
                    ->where('content', $data['content'])
                    ->where('rating', $data['rating'])
                    ->first();
            }
            $persistData = $this->filterPersistableData($data, $tableColumns);

            if ($testimonial) {
                $testimonial->update($persistData);
                $result['updated']++;
                $existingFingerprints[$fingerprint] = true;

                continue;
            }

            Testimonial::create($persistData);
            $result['created']++;
            $existingFingerprints[$fingerprint] = true;
        }

        return $result;
    }

    private function loadExistingFingerprints(array $tableColumns): array
    {
        $columns = array_values(array_intersect(['source_id', 'name', 'content', 'rating'], $tableColumns));
        if (count($columns) < 3) {
            return [];
        }

        return Testimonial::query()
            ->select($columns)
            ->get()
            ->mapWithKeys(function (Testimonial $testimonial) {
                $sourceId = property_exists($testimonial, 'source_id') ? (string) ($testimonial->source_id ?? '') : '';

                return [$this->fingerprint($sourceId, (string) $testimonial->name, (string) $testimonial->content, (int) $testimonial->rating) => true];
            })
            ->all();
    }

    private function filterPersistableData(array $data, array $tableColumns): array
    {
        $persistable = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $tableColumns, true)) {
                $persistable[$key] = $value;
            }
        }

        return $persistable;
    }

    private function extractReviews(array $payload): array
    {
        if (isset($payload['reviews']) && is_array($payload['reviews'])) {
            return $payload['reviews'];
        }

        if (isset($payload['place']['reviews']) && is_array($payload['place']['reviews'])) {
            return $payload['place']['reviews'];
        }

        if (isset($payload['result']['reviews']) && is_array($payload['result']['reviews'])) {
            return $payload['result']['reviews'];
        }

        if (array_is_list($payload)) {
            return $payload;
        }

        return [];
    }

    private function normalizeReview(array $review, array $place, array $summary, array $payloadMetadata, int $sortOrder): ?array
    {
        $name = $this->cleanText($review['reviewer_name'] ?? $review['author_name'] ?? Arr::get($review, 'user.name') ?? Arr::get($review, 'reviewer.displayName') ?? 'Khách hàng Google');
        $content = $this->cleanText($review['text'] ?? $review['comment'] ?? $review['review_text'] ?? '');
        $rating = (int) ($review['rating'] ?? 0);

        if ($rating < 1 || $rating > 5) {
            return null;
        }

        $sourceId = $this->cleanText($review['review_id'] ?? $review['id'] ?? '');
        if ($sourceId === '') {
            $sourceId = 'fingerprint:'.sha1(Str::lower($name).'|'.Str::lower($content).'|'.$rating.'|'.($review['relative_date'] ?? $review['time'] ?? ''));
        }

        $avatar = $this->cleanUrl($review['reviewer_avatar'] ?? $review['profile_photo_url'] ?? Arr::get($review, 'user.photo') ?? Arr::get($review, 'reviewer.profilePhotoUrl'));
        $relativeDate = $this->cleanText($review['relative_date'] ?? $review['relative_time_description'] ?? '');
        $placeName = $this->cleanText($place['name'] ?? $summary['name'] ?? '');
        $sourceUrl = $this->cleanUrl($review['source_url'] ?? $review['author_url'] ?? $place['google_map_url'] ?? $summary['google_map_url'] ?? Arr::get($payloadMetadata, 'resolvedPlaceUrl'));
        $reviewMedia = $this->normalizeMedia($review['media'] ?? []);
        $reviewMedia = array_merge($reviewMedia, $this->normalizeMedia($review['images'] ?? []));
        $reviewMedia = array_merge($reviewMedia, $this->normalizeMedia($review['photos'] ?? []));
        $reviewMedia = array_values(array_unique($reviewMedia));

        return [
            'name' => Str::limit($name, 150, ''),
            'role' => Str::limit($relativeDate ? 'Google · '.$relativeDate : ($placeName ? 'Google · '.$placeName : 'Google review'), 150, ''),
            'content' => $content,
            'rating' => $rating,
            'avatar' => $avatar,
            'is_active' => true,
            'sort_order' => $sortOrder,
            'source' => 'google',
            'source_id' => Str::limit($sourceId, 191, ''),
            'place_id' => Str::limit($this->cleanText($place['id'] ?? ''), 191, ''),
            'place_name' => Str::limit($placeName, 255, ''),
            'reviewer_name' => Str::limit($name, 150, ''),
            'reviewer_avatar' => $avatar,
            'relative_date' => Str::limit($relativeDate, 150, ''),
            'owner_response' => $review['owner_response'] ?? null,
            'review_media' => $reviewMedia,
            'source_url' => $sourceUrl,
            'source_created_at' => $this->parseSourceCreatedAt($review),
            'place_data' => $place ?: null,
            'summary_data' => $summary ?: null,
            'metadata' => [
                'review' => $review,
                'payload' => $payloadMetadata,
            ],
        ];
    }

    private function normalizeMedia(mixed $media): array
    {
        if (! is_array($media)) {
            return [];
        }

        $result = [];
        foreach ($media as $item) {
            if (is_string($item) && filter_var($item, FILTER_VALIDATE_URL)) {
                $result[] = $item;

                continue;
            }

            if (is_array($item)) {
                $url = $item['url'] ?? $item['thumbnail_url'] ?? $item['source_url'] ?? null;
                if (is_string($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                    $result[] = $url;
                }
            }
        }

        return $result;
    }

    private function parseSourceCreatedAt(array $review): ?Carbon
    {
        $value = $review['time'] ?? $review['created_at'] ?? null;
        if (! $value) {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::createFromTimestamp((int) $value);
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function cleanText(mixed $value): string
    {
        if (! is_scalar($value)) {
            return '';
        }

        return trim((string) $value);
    }

    private function fingerprint(string $sourceId, string $name, string $content, int $rating): string
    {
        return sha1($sourceId.'|'.Str::lower(trim($name)).'|'.Str::lower(trim($content)).'|'.$rating);
    }

    private function cleanUrl(mixed $value): ?string
    {
        $url = $this->cleanText($value);
        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        return $url;
    }
}
