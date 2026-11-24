<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\News;
use App\Models\Setting;

class NewsController extends Controller
{
    public function index()
    {
        $newsCategoryIds = News::where('is_published', true)
            ->whereNotNull('category_id')
            ->distinct()
            ->pluck('category_id');

        $categories = Category::where('is_active', true)
            ->whereIn('id', $newsCategoryIds)
            ->orderBy('sort_order')
            ->get();
        $categorySlug = request('danh-muc');

        $query = News::where('is_published', true)->with('category');

        if ($categorySlug) {
            $cat = Category::where('slug', $categorySlug)->firstOrFail();
            $query->where('category_id', $cat->id);
        } else {
            $cat = null;
        }

        $perPage = (int) Setting::get('news_per_page', 8);
        $posts = $query->orderByDesc('id')->paginate($perPage)->withQueryString();

        return view('news.index', compact('posts', 'categories', 'cat'));
    }

    public function show(string $slug)
    {
        $post = News::where('slug', $slug)->where('is_published', true)->with('category')->firstOrFail();
        $post->increment('views');

        $limit = (int) Setting::get('news_related_count', 4);
        $other = News::where('id', '!=', $post->id)
            ->where('is_published', true)
            ->orderByDesc('id')->limit($limit)->get();

        return view('news.show', compact('post', 'other'));
    }
}
