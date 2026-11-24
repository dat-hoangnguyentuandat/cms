<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('html_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('position', ['head', 'body_start', 'body_end']);
            $table->string('description')->nullable();
            $table->longText('content');
            $table->boolean('is_active')->default(true);
            $table->boolean('only_homepage')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('html_tags');
    }
};
