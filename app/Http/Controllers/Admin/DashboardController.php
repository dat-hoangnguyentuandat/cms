<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Contact;
use App\Models\News;
use App\Models\Product;
use App\Models\Testimonial;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'products' => Product::count(),
            'categories' => Category::count(),
            'news' => News::count(),
            'contacts' => Contact::count(),
            'unread' => Contact::where('is_read', false)->count(),
            'testimonials' => Testimonial::count(),
        ];
        $recentContacts = Contact::orderByDesc('id')->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'recentContacts'));
    }
}
