<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('label', 150);
            $table->enum('type', ['custom', 'page', 'news_list', 'product_list', 'service_list', 'news_cat', 'product_cat', 'service_cat'])->default('custom');
            $table->string('url', 500)->nullable();
            $table->enum('target', ['_self', '_blank'])->default('_self');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('menu_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
