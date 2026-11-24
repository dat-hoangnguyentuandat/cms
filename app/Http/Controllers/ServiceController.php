<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $serviceCategoryIds = Service::where('is_active', true)
            ->whereNotNull('category_id')
            ->distinct()
            ->pluck('category_id');

        $categories = Category::where('is_active', true)
            ->whereIn('id', $serviceCategoryIds)
            ->orderBy('sort_order')
            ->get();

        $query = Service::with('category')->where('is_active', true);

        $activeSlug = $request->query('cat');
        if ($activeSlug && $cat = $categories->firstWhere('slug', $activeSlug)) {
            $query->where('category_id', $cat->id);
        }

        if ($keyword = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('short_desc', 'like', "%{$keyword}%");
            });
        }

        $services = $query->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->paginate((int) Setting::get('services_per_page', 8))
            ->withQueryString();

        return view('services.index', compact('services', 'categories', 'activeSlug', 'keyword'));
    }

    public function show(string $slug)
    {
        $service = Service::with('category')
            ->where('slug', $slug)->where('is_active', true)->firstOrFail();

        $service->increment('views');

        $related = Service::where('category_id', $service->category_id)
            ->where('id', '!=', $service->id)
            ->where('is_active', true)
            ->orderByDesc('id')
            ->limit((int) Setting::get('services_related_count', 4))
            ->get();

        return view('service', compact('service', 'related'));
    }
}
