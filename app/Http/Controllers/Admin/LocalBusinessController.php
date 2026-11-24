<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LocalBusiness;
use App\Services\GooglePlacesCrawlerClient;
use App\Services\LocalBusinessService;
use App\Services\StructuredDataGenerator;
use Illuminate\Http\Request;

class LocalBusinessController extends Controller
{
    public function __construct(
        private LocalBusinessService $businessService,
        private StructuredDataGenerator $structuredDataGenerator
    ) {}

    public function index()
    {
        $businesses = LocalBusiness::orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.local-businesses.index', compact('businesses'));
    }

    public function create()
    {
        return view('admin.local-businesses.form');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->boolean('is_primary')) {
            LocalBusiness::where('is_primary', true)->update(['is_primary' => false]);
        }

        LocalBusiness::create($data);

        return redirect()->route('admin.local-businesses.index')->with('success', 'Đã thêm thông tin doanh nghiệp.');
    }

    public function edit(LocalBusiness $localBusiness)
    {
        return view('admin.local-businesses.form', compact('localBusiness'));
    }

    public function update(Request $request, LocalBusiness $localBusiness)
    {
        $data = $this->validated($request);

        if ($request->boolean('is_primary')) {
            LocalBusiness::where('id', '!=', $localBusiness->id)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }

        $localBusiness->update($data);

        return redirect()->route('admin.local-businesses.index')->with('success', 'Đã cập nhật.');
    }

    public function destroy(LocalBusiness $localBusiness)
    {
        $localBusiness->delete();

        return redirect()->route('admin.local-businesses.index')->with('success', 'Đã xóa.');
    }

    public function importFromCrawl(Request $request, GooglePlacesCrawlerClient $crawler)
    {
        $request->validate([
            'google_map_url' => 'required|url',
            'api_key' => 'nullable|string',
            'set_as_primary' => 'boolean',
        ]);

        try {
            $payload = $crawler->crawl(
                $request->input('google_map_url'),
                max: 1,
                apiKey: $request->input('api_key')
            );

            $business = $this->businessService->importFromGooglePlaces(
                $payload,
                $request->boolean('set_as_primary')
            );

            $business->syncAggregateRatingFromTestimonials();

            return redirect()
                ->route('admin.local-businesses.edit', $business)
                ->with('success', 'Đã import thành công từ Google Places. Vui lòng kiểm tra và điều chỉnh thông tin.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Import thất bại: ' . $e->getMessage());
        }
    }

    public function importFromJsonLd(Request $request)
    {
        $request->validate([
            'json_ld' => 'required|string',
            'set_as_primary' => 'boolean',
        ]);

        try {
            $jsonLd = json_decode($request->input('json_ld'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('JSON không hợp lệ: ' . json_last_error_msg());
            }

            $business = $this->businessService->importFromJsonLd(
                $jsonLd,
                $request->boolean('set_as_primary')
            );

            return redirect()
                ->route('admin.local-businesses.edit', $business)
                ->with('success', 'Đã import thành công từ JSON-LD. Vui lòng kiểm tra và điều chỉnh thông tin.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Import thất bại: ' . $e->getMessage());
        }
    }

    public function syncRating(LocalBusiness $localBusiness)
    {
        $localBusiness->syncAggregateRatingFromTestimonials();

        return back()->with('success', 'Đã đồng bộ rating từ testimonials.');
    }

    public function preview(LocalBusiness $localBusiness)
    {
        $jsonLd = $this->structuredDataGenerator->generateLocalBusiness($localBusiness);
        $scriptTag = $this->structuredDataGenerator->generateScriptTag($localBusiness);

        return view('admin.local-businesses.preview', compact('localBusiness', 'jsonLd', 'scriptTag'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'nullable|url',
            'logo_url' => 'nullable|url',
            'image_url' => 'nullable|url',
            'telephone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|array',
            'address.street' => 'nullable|string',
            'address.city' => 'nullable|string',
            'address.state' => 'nullable|string',
            'address.postal_code' => 'nullable|string',
            'address.country' => 'nullable|string',
            'geo_coordinates' => 'nullable|array',
            'geo_coordinates.latitude' => 'nullable|numeric',
            'geo_coordinates.longitude' => 'nullable|numeric',
            'price_range' => 'nullable|string|max:20',
            'payment_accepted' => 'nullable|array',
            'currencies_accepted' => 'nullable|array',
            'opening_hours' => 'nullable|array',
            'social_profiles' => 'nullable|array',
            'schema_type' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
        ]);
    }
}
