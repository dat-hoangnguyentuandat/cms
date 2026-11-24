<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropForeignIdFor(Category::class);
            $table->dropColumn('category_id');
        });
    }
};
