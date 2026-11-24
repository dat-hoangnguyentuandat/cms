<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicStorageController extends Controller
{
    /**
     * Serve public-disk files when the web server's public/storage link is
     * unavailable. A healthy symlink is still served directly by Nginx/Apache.
     */
    public function show(string $path): BinaryFileResponse
    {
        $path = str_replace('\\', '/', trim($path, '/'));

        if ($path === '' || str_contains($path, "\0") || preg_match('~(?:^|/)\.\.(?:/|$)~', $path)) {
            abort(404);
        }

        $root = realpath(Storage::disk('public')->path(''));
        $file = $root ? realpath($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path)) : false;

        if ($root === false || $file === false || ! is_file($file)) {
            abort(404);
        }

        $rootPrefix = rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        if (! str_starts_with($file, $rootPrefix)) {
            abort(404);
        }

        return response()->file($file, [
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
