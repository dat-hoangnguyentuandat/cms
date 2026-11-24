<?php

namespace App\Services\Admin;

use App\Models\AdminUser;

class AdminMenuRegistry
{
    private array $groups = [];

    public function register(string $group, array $item): void
    {
        $this->groups[$group][] = [
            'route' => $item['route'],
            'icon' => $item['icon'] ?? 'dashboard',
            'label' => $item['label'],
            'permission' => $item['permission'] ?? null,
        ];
    }

    public function groupsFor(?AdminUser $user): array
    {
        $isSuperAdmin = $user && $user->role === 'admin';
        $groups = [];

        foreach ($this->groups as $group => $items) {
            foreach ($items as $item) {
                $permission = $item['permission'] ?? null;
                if ($permission && ! $isSuperAdmin && ! $user?->hasAdminPermission($permission)) {
                    continue;
                }

                $groups[$group][] = $item;
            }
        }

        return array_filter($groups);
    }
}
