<?php

namespace App\Console\Commands;

use App\Models\PlatformAdmin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class PlatformAdminCommand extends Command
{
    protected $signature = 'platform:admin
        {username : Platform administrator username}
        {--email= : Platform administrator email}
        {--name=Platform Admin : Display name}
        {--password= : Password with at least 12 characters}
        {--disable : Disable an existing account}';

    protected $description = 'Create, update or disable a central Platform Admin account';

    public function handle(): int
    {
        $data = [
            'username' => strtolower(trim((string) $this->argument('username'))),
            'email' => strtolower(trim((string) ($this->option('email') ?: $this->argument('username').'@admin.com'))),
            'name' => trim((string) $this->option('name')),
            'password' => (string) $this->option('password'),
        ];

        if ($this->option('disable')) {
            $admin = PlatformAdmin::query()->where('username', $data['username'])->first();
            if (! $admin) {
                $this->error('Platform Admin account not found.');

                return self::FAILURE;
            }

            $admin->update(['is_active' => false]);
            $this->info("Disabled {$admin->email}.");

            return self::SUCCESS;
        }

        $validator = Validator::make($data, [
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._-]+$/'],
            'email' => ['required', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:12'],
        ]);
        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        $admin = PlatformAdmin::query()->updateOrCreate(
            ['username' => $data['username']],
            [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'is_active' => true,
            ],
        );
        $this->info("Platform Admin ready: {$admin->username}");

        return self::SUCCESS;
    }
}
