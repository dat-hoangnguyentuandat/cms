<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UnsplashImageService;
use Illuminate\Http\Request;

class ImageCrawlerController extends Controller
{
    public function __construct(
        private UnsplashImageService $unsplash
    ) {}

    public function index()
    {
        return view('admin.image-crawler.index');
    }

    public function crawl(Request $request)
    {
        set_time_limit(300);

        try {
            $downloaded = $this->unsplash->crawlSpaImages();

            return response()->json([
                'success' => true,
                'message' => 'Downloaded '.count($downloaded).' images successfully',
                'images' => $downloaded,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }
}
