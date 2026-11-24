<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Faq;
use App\Models\HomepageBlock;
use App\Models\News;
use App\Models\Product;
use App\Models\Service;
use App\Models\Testimonial;

class HomeController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->where('is_active', true)
            ->orderByDesc('is_featured')->orderByDesc('id')->limit(12)->get();
        $services = Service::with('category')->where('is_active', true)
            ->orderByDesc('is_featured')->orderByDesc('id')->limit(12)->get();
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $testimonials = Testimonial::where('is_active', true)->orderBy('sort_order')->limit(6)->get();
        $faqs = Faq::where('is_active', true)->orderBy('sort_order')->get();
        $news = News::where('is_published', true)->orderByDesc('id')->limit(8)->get();
        $homepageBlocks = HomepageBlock::where('theme_name', active_theme())->where('is_active', true)->orderBy('sort_order')->get()->groupBy('zone');

        return view('home', compact('products', 'services', 'categories', 'testimonials', 'faqs', 'news', 'homepageBlocks'));
    }
}
