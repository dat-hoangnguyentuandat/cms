<?php

namespace App\Services;

use App\Models\MenuItem;
use Illuminate\Support\Facades\DB;

class FrontendMenuInstaller
{
    /**
     * Add a top-level frontend menu item, or repair the matching item if it
     * already exists. The returned status is suitable for an admin notice.
     */
    public function install(string $label, string $url, string $slug): string
    {
        return DB::transaction(function () use ($label, $url, $slug): string {
            $item = MenuItem::query()
                ->whereNull('parent_id')
                ->where(function ($query) use ($url, $slug) {
                    $query->where('url', $url)->orWhere('slug', $slug);
                })
                ->lockForUpdate()
                ->first();

            if (! $item) {
                MenuItem::create([
                    'parent_id' => null,
                    'label' => $label,
                    'type' => 'custom',
                    'url' => $url,
                    'target' => '_self',
                    'sort_order' => ((int) MenuItem::query()->whereNull('parent_id')->max('sort_order')) + 1,
                    'is_active' => true,
                ]);

                return 'created';
            }

            $item->fill([
                'label' => $label,
                'type' => 'custom',
                'url' => $url,
                'target' => '_self',
                'is_active' => true,
            ]);

            if (! $item->isDirty()) {
                return 'unchanged';
            }

            $item->save();

            return 'updated';
        });
    }

    /**
     * Hide every matching top-level menu item. Keeping the records allows a
     * later install to restore the item without losing its menu position.
     */
    public function remove(string $url, string $slug): string
    {
        return DB::transaction(function () use ($url, $slug): string {
            $items = MenuItem::query()
                ->whereNull('parent_id')
                ->where(function ($query) use ($url, $slug) {
                    $query->where('url', $url)->orWhere('slug', $slug);
                })
                ->lockForUpdate()
                ->get();

            $changed = false;
            foreach ($items as $item) {
                if (! $item->is_active) {
                    continue;
                }

                $item->update(['is_active' => false]);
                $changed = true;
            }

            return $changed ? 'removed' : 'unchanged';
        });
    }
}
