<?php

namespace App\Console\Commands;

use App\Services\Theme\ThemeManager;
use App\Services\Theme\ThemeWidgetSynchronizer;
use Illuminate\Console\Command;

class SyncThemeWidgetsCommand extends Command
{
    protected $signature = 'theme:widgets-sync {name} {--force : Overwrite existing theme widget files from the standard set}';

    protected $description = 'Copy the standard widget set into a theme so it owns a full, isolated copy';

    public function handle(ThemeManager $manager, ThemeWidgetSynchronizer $sync): int
    {
        $name = $this->argument('name');

        if (! $manager->exists($name)) {
            $this->error("Theme '{$name}' chưa được cài đặt.");

            return self::FAILURE;
        }

        $result = $sync->sync($name, (bool) $this->option('force'));

        $this->info(sprintf(
            "Đồng bộ widget cho '%s': copied %d, skipped %d (đã có), tổng %d.",
            $name,
            $result['copied'],
            $result['skipped'],
            $result['total'],
        ));

        if (! $this->option('force') && $result['skipped'] > 0) {
            $this->line('Dùng --force để ghi đè các widget đã tùy chỉnh trong theme.');
        }

        return self::SUCCESS;
    }
}
