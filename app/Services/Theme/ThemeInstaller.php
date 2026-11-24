<?php

namespace App\Services\Theme;

use App\Exceptions\ThemeInstallException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use ZipArchive;

class ThemeInstaller
{
    public function __construct(
        private string $themesPath,
        private string $publicAssetsPath,
        private ThemeAssetVersion $assetVersions,
    ) {}

    /**
     * Install a theme from an uploaded zip file.
     * Returns parsed theme metadata.
     *
     * @throws ThemeInstallException
     */
    public function install(UploadedFile $file): array
    {
        if ($file->getClientOriginalExtension() !== 'zip' || $file->getMimeType() !== 'application/zip') {
            if (! in_array($file->getMimeType(), ['application/zip', 'application/x-zip-compressed', 'application/octet-stream'])) {
                throw new ThemeInstallException('Tệp tải lên phải là file .zip hợp lệ.');
            }
        }

        $tmpDir = storage_path('app/theme-tmp/'.uniqid('theme_', true));
        File::ensureDirectoryExists($tmpDir);

        try {
            $this->extractZip($file->getRealPath(), $tmpDir);

            $root = $this->resolveThemeRoot($tmpDir);
            $meta = $this->readMeta($root);

            $name = $meta['name'];
            $target = $this->themesPath.DIRECTORY_SEPARATOR.$name;

            if (File::isDirectory($target)) {
                File::deleteDirectory($target);
            }
            File::ensureDirectoryExists(dirname($target));
            File::moveDirectory($root, $target);

            $this->publishAssets($name, $target);

            return $meta;
        } finally {
            if (File::isDirectory($tmpDir)) {
                File::deleteDirectory($tmpDir);
            }
        }
    }

    private function extractZip(string $zipPath, string $destination): void
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new ThemeInstallException('Không thể mở file zip.');
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if ($entry === false) {
                continue;
            }
            $normalized = str_replace('\\', '/', $entry);
            if (str_contains($normalized, '..')) {
                $zip->close();
                throw new ThemeInstallException('Zip chứa đường dẫn không an toàn.');
            }
        }

        if (! $zip->extractTo($destination)) {
            $zip->close();
            throw new ThemeInstallException('Giải nén zip thất bại.');
        }
        $zip->close();
    }

    /**
     * Some zips wrap content in a top-level folder (eg. my-theme/...)
     * Detect and return the dir that actually contains theme.json.
     */
    private function resolveThemeRoot(string $extracted): string
    {
        if (File::exists($extracted.'/theme.json')) {
            return $extracted;
        }

        $entries = array_values(array_filter(File::directories($extracted)));
        if (count($entries) === 1 && File::exists($entries[0].'/theme.json')) {
            return $entries[0];
        }

        throw new ThemeInstallException('Không tìm thấy theme.json trong zip.');
    }

    private function readMeta(string $root): array
    {
        $raw = File::get($root.'/theme.json');
        $meta = json_decode($raw, true);

        if (! is_array($meta)) {
            throw new ThemeInstallException('theme.json không phải JSON hợp lệ.');
        }

        $name = $meta['name'] ?? null;
        if (! is_string($name) || ! preg_match('/^[a-z0-9][a-z0-9_\-]{1,63}$/', $name)) {
            throw new ThemeInstallException('Trường "name" trong theme.json không hợp lệ (slug a-z, 0-9, _ , -).');
        }

        if (! File::isDirectory($root.'/views')) {
            throw new ThemeInstallException('Theme thiếu thư mục views/.');
        }

        $meta['version'] = $meta['version'] ?? '1.0.0';
        $meta['author'] = $meta['author'] ?? 'Unknown';
        $meta['preview'] = $meta['preview'] ?? null;

        return $meta;
    }

    /**
     * Make a theme's assets reachable at /theme-assets/{name}.
     * Symlink on POSIX; copy on Windows (where symlinks need admin).
     */
    public function publishAssets(string $name, string $themeDir): void
    {
        $assetSrc = $themeDir.DIRECTORY_SEPARATOR.'assets';
        if (! File::isDirectory($assetSrc)) {
            return;
        }

        $linkPath = $this->publicAssetsPath.DIRECTORY_SEPARATOR.$name;
        File::ensureDirectoryExists($this->publicAssetsPath);

        if (is_link($linkPath)) {
            @unlink($linkPath);
        } elseif (File::isDirectory($linkPath)) {
            File::deleteDirectory($linkPath);
        }

        if ($this->isWindows()) {
            File::copyDirectory($assetSrc, $linkPath);
        } elseif (! @symlink($assetSrc, $linkPath)) {
            File::copyDirectory($assetSrc, $linkPath);
        }

        // A release id is more reliable than filemtime because zip extraction,
        // rsync and CDN deployments may preserve the old timestamp.
        $this->assetVersions->bump($name);
    }

    public function unpublishAssets(string $name): void
    {
        $linkPath = $this->publicAssetsPath.DIRECTORY_SEPARATOR.$name;
        if (is_link($linkPath)) {
            @unlink($linkPath);
        } elseif (File::isDirectory($linkPath)) {
            File::deleteDirectory($linkPath);
        }

        $this->assetVersions->forget($name);
    }

    private function isWindows(): bool
    {
        return strncasecmp(PHP_OS, 'WIN', 3) === 0;
    }
}
