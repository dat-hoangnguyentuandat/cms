<?php

use App\Models\Setting;
use App\Services\Theme\ThemeManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_blocks', function (Blueprint $table) {
            $table->string('theme_name', 80)->default(ThemeManager::DEFAULT_THEME)->after('id');
            $table->index(['theme_name', 'zone', 'sort_order']);
        });

        $activeTheme = Setting::get(ThemeManager::SETTING_KEY, ThemeManager::DEFAULT_THEME) ?: ThemeManager::DEFAULT_THEME;
        DB::table('homepage_blocks')->update(['theme_name' => $activeTheme]);
    }

    public function down(): void
    {
        Schema::table('homepage_blocks', function (Blueprint $table) {
            $table->dropIndex(['theme_name', 'zone', 'sort_order']);
            $table->dropColumn('theme_name');
        });
    }
};
