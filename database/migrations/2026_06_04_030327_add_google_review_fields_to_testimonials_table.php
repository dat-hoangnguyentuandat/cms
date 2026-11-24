<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('source', 50)->nullable()->after('sort_order')->index();
            $table->string('source_id', 191)->nullable()->after('source');
            $table->string('place_id', 191)->nullable()->after('source_id');
            $table->string('place_name', 255)->nullable()->after('place_id');
            $table->string('reviewer_name', 150)->nullable()->after('place_name');
            $table->text('reviewer_avatar')->nullable()->after('reviewer_name');
            $table->string('relative_date', 150)->nullable()->after('reviewer_avatar');
            $table->longText('owner_response')->nullable()->after('relative_date');
            $table->json('review_media')->nullable()->after('owner_response');
            $table->text('source_url')->nullable()->after('review_media');
            $table->timestamp('source_created_at')->nullable()->after('source_url');
            $table->json('place_data')->nullable()->after('source_created_at');
            $table->json('summary_data')->nullable()->after('place_data');
            $table->json('metadata')->nullable()->after('summary_data');
            $table->unique(['source', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropUnique(['source', 'source_id']);
            $table->dropIndex(['source']);
            $table->dropColumn([
                'source',
                'source_id',
                'place_id',
                'place_name',
                'reviewer_name',
                'reviewer_avatar',
                'relative_date',
                'owner_response',
                'review_media',
                'source_url',
                'source_created_at',
                'place_data',
                'summary_data',
                'metadata',
            ]);
        });
    }
};
