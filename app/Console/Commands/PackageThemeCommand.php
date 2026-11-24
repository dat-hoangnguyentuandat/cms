<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class PackageThemeCommand extends Command
{
    protected $signature = 'theme:package {name} {--out=}';

    protected $description = 'Package a theme directory under themes/ into a distributable .zip';

    public function handle(): int
    {
        $name = $this->argument('name');
        $src = base_path("themes/{$name}");

        if (! File::isDirectory($src)) {
            $this->error("Theme '{$name}' does not exist at {$src}.");

            return self::FAILURE;
        }
        if (! File::exists($src.'/theme.json')) {
            $this->error("theme.json missing inside {$src}.");

            return self::FAILURE;
        }

        $out = $this->option('out') ?: storage_path("app/themes/{$name}.zip");
        File::ensureDirectoryExists(dirname($out));
        if (File::exists($out)) {
            File::delete($out);
        }

        $zip = new ZipArchive;
        if ($zip->open($out, ZipArchive::CREATE) !== true) {
            $this->error("Cannot open {$out} for writing.");

            return self::FAILURE;
        }

        $base = realpath($src);
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $count = 0;
        foreach ($it as $f) {
            if ($f->isFile()) {
                $rel = ltrim(str_replace('\\', '/', substr($f->getRealPath(), strlen($base))), '/');
                $zip->addFile($f->getRealPath(), $rel);
                $count++;
            }
        }
        $zip->close();

        $this->info("Packaged {$count} files into {$out} (".filesize($out).' bytes).');

        return self::SUCCESS;
    }
}
