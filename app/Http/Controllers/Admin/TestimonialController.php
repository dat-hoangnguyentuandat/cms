<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\GoogleReviewImportException;
use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Services\GooglePlacesCrawlerClient;
use App\Services\GoogleReviewTestimonialImporter;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $status = $request->get('status', '');
        $rating = $request->get('rating', '');
        $sortFrom = $request->get('sort_from', '');
        $sortTo = $request->get('sort_to', '');
        $from = $request->get('from', '');
        $to = $request->get('to', '');

        $query = Testimonial::orderBy('sort_order');
        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%$q%")
                    ->orWhere('role', 'like', "%$q%")
                    ->orWhere('content', 'like', "%$q%");
            });
        }
        if ($request->filled('status')) {
            $query->where('is_active', $status === '1');
        }
        if ($request->filled('rating')) {
            $query->where('rating', $rating);
        }
        if ($request->filled('sort_from')) {
            $query->where('sort_order', '>=', $sortFrom);
        }
        if ($request->filled('sort_to')) {
            $query->where('sort_order', '<=', $sortTo);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $testimonials = $query->paginate(20)->appends($request->query());

        return view('admin.testimonials.index', compact('testimonials', 'q', 'status', 'rating', 'sortFrom', 'sortTo', 'from', 'to'));
    }

    public function create()
    {
        return view('admin.testimonials.form');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('testimonials', 'public');
        }
        Testimonial::create($data);

        return redirect()->route('admin.testimonials.index')->with('success', 'Đã thêm đánh giá.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.form', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $data = $this->validated($request);
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('testimonials', 'public');
        }
        $testimonial->update($data);

        return redirect()->route('admin.testimonials.index')->with('success', 'Đã cập nhật.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('success', 'Đã xóa.');
    }

    public function importGoogleReviews(Request $request, GoogleReviewTestimonialImporter $importer)
    {
        $request->validate([
            'reviews_json' => 'required|file|max:20480',
        ]);

        try {
            $result = $importer->import($request->file('reviews_json'));
        } catch (GoogleReviewImportException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.testimonials.index')->with('success', sprintf(
            'Đã import %d đánh giá, tạo mới %d, cập nhật %d, bỏ qua %d, lỗi %d.',
            $result['total'],
            $result['created'],
            $result['updated'],
            $result['skipped'],
            $result['invalid']
        ));
    }

    public function importGoogleApi(Request $request, GooglePlacesCrawlerClient $client, GoogleReviewTestimonialImporter $importer)
    {
        $data = $request->validate([
            'api_detail_url' => 'nullable|url|max:2000|required_without:google_map_url',
            'google_map_url' => 'nullable|url|max:2000|required_without:api_detail_url',
            'max' => 'required|integer|min:1|max:100',
            'api_key' => 'nullable|string|max:255',
        ]);

        $overwrite = $request->boolean('overwrite');

        try {
            if (! empty($data['api_detail_url'])) {
                $payload = $client->detail($data['api_detail_url'], $data['api_key'] ?? null);
            } else {
                $payload = $client->crawl($data['google_map_url'], (int) $data['max'], $data['api_key'] ?? null);
            }
            $result = $importer->importPayload($payload, (int) $data['max'], $overwrite);
        } catch (GoogleReviewImportException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $sourceLabel = ! empty($data['api_detail_url']) ? 'API detail URL' : 'API crawl';
        $modeLabel = $overwrite ? ' (ghi đè toàn bộ đánh giá Google cũ)' : '';

        return redirect()->route('admin.testimonials.index')->with('success', sprintf(
            'Đã import %d đánh giá từ %s%s, tạo mới %d, cập nhật %d, bỏ qua %d, lỗi %d.',
            $result['total'],
            $sourceLabel,
            $modeLabel,
            $result['created'],
            $result['updated'],
            $result['skipped'],
            $result['invalid']
        ));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|max:150',
            'role' => 'nullable|max:150',
            'content' => 'required',
            'rating' => 'required|integer|min:1|max:5',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'source' => 'nullable|max:50',
            'source_id' => 'nullable|max:191',
            'relative_date' => 'nullable|max:150',
            'source_url' => 'nullable|max:1000',
            'owner_response' => 'nullable',
            'review_media' => 'nullable|array',
            'review_media.*' => 'nullable|max:1000',
            'place_data' => 'nullable|array',
            'summary_data' => 'nullable|array',
            'summary_data.about_items' => 'nullable|array',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['review_media'] = $this->cleanList($request->input('review_media', []));
        $data['place_data'] = $this->cleanArray($request->input('place_data', []));
        $data['summary_data'] = $this->cleanArray($request->input('summary_data', []));
        $data['summary_data']['about_items'] = $this->cleanRepeater($request->input('summary_data.about_items', []));
        $data['place_id'] = $data['place_data']['id'] ?? null;
        $data['place_name'] = $data['place_data']['name'] ?? null;
        $data['reviewer_name'] = $data['name'];
        $data['reviewer_avatar'] = $request->input('reviewer_avatar') ?: null;

        return $data;
    }

    private function cleanList(array $items): array
    {
        return array_values(array_filter(array_map(fn ($item) => trim((string) $item), $items)));
    }

    private function cleanArray(array $items): array
    {
        $clean = [];
        foreach ($items as $key => $value) {
            if (is_array($value)) {
                $clean[$key] = $value;

                continue;
            }
            $value = trim((string) $value);
            if ($value !== '') {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }

    private function cleanRepeater(array $items): array
    {
        $clean = [];
        foreach ($items as $item) {
            $label = trim((string) ($item['label'] ?? ''));
            $text = trim((string) ($item['text'] ?? ''));
            if ($label !== '' || $text !== '') {
                $clean[] = ['label' => $label, 'text' => $text];
            }
        }

        return $clean;
    }
}
