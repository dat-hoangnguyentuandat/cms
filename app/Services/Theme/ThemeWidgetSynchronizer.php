<?php

namespace App\Services\Theme;

use Illuminate\Support\Facades\File;

/**
 * Copies the standard widget set (public/admin-assets/widgets) into a theme's own
 * widgets folder (themes/{slug}/widgets) so every theme owns a full, isolated
 * copy. Editing a theme's widget never touches the shared standard set.
 *
 * Non-destructive by default: existing theme widget files are preserved so
 * theme-specific customizations are never overwritten. Pass $force to refresh
 * every file from the standard set.
 */
class ThemeWidgetSynchronizer
{
    public function __construct(
        private string $standardWidgetsPath,
        private string $themesPath,
    ) {}

    /**
     * @return array{copied:int, skipped:int, total:int}
     */
    public function sync(string $theme, bool $force = false): array
    {
        $copied = 0;
        $skipped = 0;
        $total = 0;

        if (! File::isDirectory($this->standardWidgetsPath)) {
            return ['copied' => 0, 'skipped' => 0, 'total' => 0];
        }

        $themeWidgetsRoot = $this->themesPath.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR.'widgets';
        File::ensureDirectoryExists($themeWidgetsRoot);

        foreach (File::allFiles($this->standardWidgetsPath) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relative = ltrim(str_replace('\\', '/', substr($file->getRealPath(), strlen(realpath($this->standardWidgetsPath)))), '/');
            $dest = $themeWidgetsRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
            $total++;

            if (File::exists($dest) && ! $force) {
                $skipped++;

                continue;
            }

            File::ensureDirectoryExists(dirname($dest));
            File::copy($file->getRealPath(), $dest);
            $copied++;
        }

        return ['copied' => $copied, 'skipped' => $skipped, 'total' => $total];
    }
}
