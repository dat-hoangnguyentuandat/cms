<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) Setting::get('testimonials_per_page', 9);

        $testimonials = Testimonial::query()
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN (reviewer_avatar IS NOT NULL AND reviewer_avatar != '') OR (avatar IS NOT NULL AND avatar != '') THEN 0 ELSE 1 END")
            ->orderByDesc('source_created_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        $googleReviewUrl = Setting::get('google_review_url');

        if (! $googleReviewUrl) {
            $sourceUrl = Testimonial::where('source', 'google')
                ->whereNotNull('source_url')
                ->value('source_url');

            if ($sourceUrl) {
                $parsedUrl = parse_url($sourceUrl);
                $path = rtrim($parsedUrl['path'], '/').'/review';
                $query = isset($parsedUrl['query']) ? '?'.$parsedUrl['query'] : '';
                $googleReviewUrl = $parsedUrl['scheme'].'://'.$parsedUrl['host'].$path.$query;
            } else {
                $placeId = Testimonial::whereNotNull('place_id')->value('place_id');
                $googleReviewUrl = $placeId
                    ? "https://search.google.com/local/writereview?placeid={$placeId}"
                    : null;
            }
        }

        return view('testimonials', compact('testimonials', 'googleReviewUrl'));
    }
}
