<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = (string) config('tenancy.central_connection', 'central');

        Schema::connection($connection)->create('platform_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_admin_id')->nullable()->constrained('platform_admins')->nullOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('action', 120)->index();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::connection((string) config('tenancy.central_connection', 'central'))
            ->dropIfExists('platform_audit_logs');
    }
};
