<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 200);
            $table->string('slug', 220)->unique();
            $table->string('sku', 60)->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('short_desc', 500)->nullable();
            $table->longText('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->string('image')->nullable();
            $table->text('gallery')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
