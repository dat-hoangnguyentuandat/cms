<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = (string) config('tenancy.central_connection', 'central');

        Schema::connection($connection)->create('tenant_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->enum('package_type', ['theme', 'plugin']);
            $table->string('package_name');
            $table->unique(['tenant_id', 'package_type', 'package_name']);
            $table->index(['package_type', 'package_name']);
        });
    }

    public function down(): void
    {
        $connection = (string) config('tenancy.central_connection', 'central');
        Schema::connection($connection)->dropIfExists('tenant_packages');
    }
};
