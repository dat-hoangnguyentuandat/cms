<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('local_businesses', function (Blueprint $table) {
            $table->id();

            // Basic Info
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('image_url')->nullable();

            // Contact
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->json('address')->nullable(); // {street, city, state, postal_code, country}

            // Location
            $table->json('geo_coordinates')->nullable(); // {latitude, longitude}
            $table->string('google_place_id')->nullable()->unique();
            $table->string('google_map_url')->nullable();

            // Business Details
            $table->string('price_range')->nullable(); // $, $$, $$$
            $table->json('payment_accepted')->nullable(); // ['cash', 'credit_card', etc.]
            $table->json('currencies_accepted')->nullable(); // ['USD', 'VND', etc.]
            $table->json('opening_hours')->nullable(); // structured opening hours

            // Ratings & Reviews
            $table->decimal('aggregate_rating', 3, 2)->nullable(); // 0.00 - 5.00
            $table->integer('review_count')->default(0);

            // Social Media
            $table->json('social_profiles')->nullable(); // {facebook, instagram, twitter, linkedin, etc.}

            // SEO
            $table->string('schema_type')->default('LocalBusiness'); // LocalBusiness, Restaurant, Store, etc.

            // Metadata
            $table->json('metadata')->nullable(); // raw Google Places data or custom data

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_primary')->default(false); // primary location

            $table->timestamps();

            // Indexes
            $table->index('is_active');
            $table->index('is_primary');
            $table->index('google_place_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('local_businesses');
    }
};
