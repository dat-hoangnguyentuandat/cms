<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->string('title', 180);
            $table->string('version', 50)->default('1.0.0');
            $table->string('author', 180)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('path')->nullable();
            $table->json('manifest')->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('plugin_settings', function (Blueprint $table) {
            $table->id();
            $table->string('plugin_name', 120);
            $table->string('setting_key', 120);
            $table->text('setting_value')->nullable();
            $table->string('setting_group', 80)->default('general');
            $table->timestamps();

            $table->unique(['plugin_name', 'setting_key']);
            $table->index('plugin_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_settings');
        Schema::dropIfExists('plugins');
    }
};
