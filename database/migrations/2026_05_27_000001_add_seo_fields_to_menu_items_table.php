<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('keyword', 500)->nullable()->after('label');
            $table->string('title', 255)->nullable()->after('keyword');
            $table->text('description')->nullable()->after('title');
            $table->string('seo_title', 70)->nullable()->after('description');
            $table->string('seo_description', 160)->nullable()->after('seo_title');
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE menu_items MODIFY COLUMN type ENUM('custom','page','news_list','product_list','service_list','news_cat','product_cat','service_cat','contact') DEFAULT 'custom'");
        }
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['keyword', 'title', 'description', 'seo_title', 'seo_description']);
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE menu_items MODIFY COLUMN type ENUM('custom','page','news_list','product_list','service_list','news_cat','product_cat','service_cat') DEFAULT 'custom'");
        }
    }
};
