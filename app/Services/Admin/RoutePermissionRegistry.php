<?php

namespace App\Services\Admin;

class RoutePermissionRegistry
{
    private array $map = [];

    public function register(string $routeNamePrefix, string $permission): void
    {
        $this->map[$routeNamePrefix] = $permission;
    }

    public function registerMany(array $map): void
    {
        foreach ($map as $prefix => $permission) {
            $this->register($prefix, $permission);
        }
    }

    public function resolve(string $routeName): ?string
    {
        $map = $this->map;
        uksort($map, fn (string $a, string $b) => strlen($b) <=> strlen($a));

        foreach ($map as $prefix => $permission) {
            if (str_starts_with($routeName, $prefix)) {
                return $permission;
            }
        }

        return null;
    }
}
