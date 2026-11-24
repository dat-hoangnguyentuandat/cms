<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        $query = Product::with('category')->where('is_active', true);

        $activeSlug = $request->query('cat');
        if ($activeSlug && $cat = $categories->firstWhere('slug', $activeSlug)) {
            $query->where('category_id', $cat->id);
        }

        $keyword = trim((string) $request->query('q'));
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('short_desc', 'like', "%{$keyword}%");
            });
            $perPage = (int) Setting::get('search_per_page', 12);
        } else {
            $perPage = (int) Setting::get('products_per_page', 12);
        }

        $products = $query->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('products.index', compact('products', 'categories', 'activeSlug', 'keyword'));
    }

    public function show(string $slug)
    {
        $product = Product::with('category')
            ->where('slug', $slug)->where('is_active', true)->firstOrFail();

        $product->increment('views');

        $related = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->orderByDesc('id')
            ->limit((int) Setting::get('products_related_count', 4))
            ->get();

        return view('product', compact('product', 'related'));
    }
}
