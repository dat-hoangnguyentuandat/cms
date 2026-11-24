Route::prefix('admin')->middleware(['auth', 'role:admin'])->name('admin.')->group(function () {
    // Image crawler for theme
    Route::get('/image-crawler', [\App\Http\Controllers\Admin\ImageCrawlerController::class, 'index'])->name('image-crawler.index');
    Route::post('/image-crawler/crawl', [\App\Http\Controllers\Admin\ImageCrawlerController::class, 'crawl'])->name('image-crawler.crawl');
});