<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $connection = (string) config('tenancy.central_connection', 'central');

        Schema::connection($connection)->table('platform_admins', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->unique()->after('id');
        });

        $used = [];
        foreach (DB::connection($connection)->table('platform_admins')->orderBy('id')->get() as $admin) {
            $base = Str::lower((string) Str::before($admin->email, '@'));
            $base = preg_replace('/[^a-z0-9._-]/', '', $base) ?: 'admin';
            $username = substr($base, 0, 50);
            $suffix = 2;

            while (isset($used[$username])) {
                $ending = '-'.$suffix++;
                $username = substr($base, 0, 50 - strlen($ending)).$ending;
            }

            $used[$username] = true;
            DB::connection($connection)
                ->table('platform_admins')
                ->where('id', $admin->id)
                ->update(['username' => $username]);
        }
    }

    public function down(): void
    {
        $connection = (string) config('tenancy.central_connection', 'central');

        Schema::connection($connection)->table('platform_admins', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
