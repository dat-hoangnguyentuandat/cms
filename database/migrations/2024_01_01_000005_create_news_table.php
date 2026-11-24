<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title', 220);
            $table->string('slug', 240)->unique();
            $table->string('excerpt', 500)->nullable();
            $table->longText('content')->nullable();
            $table->string('image')->nullable();
            $table->string('author', 100)->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
