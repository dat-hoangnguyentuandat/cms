<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('role', 150)->nullable();
            $table->text('content');
            $table->tinyInteger('rating')->default(5);
            $table->string('avatar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question', 300);
            $table->text('answer');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 100)->unique();
            $table->text('setting_value')->nullable();
            $table->string('setting_group', 50)->default('general');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('testimonials');
    }
};
