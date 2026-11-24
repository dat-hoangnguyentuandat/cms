<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('password');
            $table->string('full_name', 100);
            $table->string('email', 150);
            $table->enum('role', ['admin', 'editor'])->default('editor');
            $table->string('avatar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_login')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_users');
    }
};
