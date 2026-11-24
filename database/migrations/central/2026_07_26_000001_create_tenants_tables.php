<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = (string) config('tenancy.central_connection', 'central');

        Schema::connection($connection)->create('tenants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true)->index();
            $table->string('db_driver', 20)->default('mysql');
            $table->string('db_host')->nullable();
            $table->unsignedInteger('db_port')->nullable();
            $table->string('db_database');
            $table->string('db_username')->nullable();
            $table->text('db_password')->nullable();
            $table->text('db_options')->nullable();
            $table->timestamps();
        });

        Schema::connection($connection)->create('tenant_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('domain')->unique();
            $table->boolean('is_primary')->default(false);
            $table->index(['tenant_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        $connection = (string) config('tenancy.central_connection', 'central');
        Schema::connection($connection)->dropIfExists('tenant_domains');
        Schema::connection($connection)->dropIfExists('tenants');
    }
};
