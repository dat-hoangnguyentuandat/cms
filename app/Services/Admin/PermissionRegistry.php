<?php

namespace App\Services\Admin;

class PermissionRegistry
{
    private array $permissions = [];

    public function register(string $key, string $label): void
    {
        $this->permissions[$key] = $label;
    }

    public function registerMany(array $permissions): void
    {
        foreach ($permissions as $key => $label) {
            $this->register($key, $label);
        }
    }

    public function all(): array
    {
        return $this->permissions;
    }
}
