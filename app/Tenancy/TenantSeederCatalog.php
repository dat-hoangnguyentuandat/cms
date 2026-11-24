<?php

namespace App\Tenancy;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use ReflectionClass;

class TenantSeederCatalog
{
    /**
     * @return list<class-string<Seeder>>
     */
    public function all(): array
    {
        $path = database_path('seeders');
        if (! File::isDirectory($path)) {
            return [];
        }

        return collect(File::allFiles($path))
            ->filter(fn ($file) => $file->getExtension() === 'php')
            ->map(function ($file): string {
                $relativePath = str_replace(['/', '\\'], '\\', $file->getRelativePathname());

                return 'Database\\Seeders\\'.substr($relativePath, 0, -4);
            })
            ->filter(function (string $class): bool {
                if (! class_exists($class) || ! is_subclass_of($class, Seeder::class)) {
                    return false;
                }

                return ! (new ReflectionClass($class))->isAbstract();
            })
            ->sortBy(fn (string $class) => class_basename($class), SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    public function contains(string $seeder): bool
    {
        return in_array($seeder, $this->all(), true);
    }
}
