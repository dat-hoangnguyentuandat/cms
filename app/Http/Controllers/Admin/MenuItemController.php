<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MenuItemController extends Controller
{
    public function toggle(MenuItem $menuItem)
    {
        $menuItem->update(['is_active' => ! $menuItem->is_active]);

        return response()->json(['is_active' => $menuItem->is_active]);
    }

    public function reorder(Request $request)
    {
        $items = $request->input('items', []);
        foreach ($items as $parentOrder => $item) {
            MenuItem::where('id', $item['id'])->update([
                'parent_id' => null,
                'sort_order' => $parentOrder,
            ]);
            foreach ($item['children'] ?? [] as $childOrder => $child) {
                MenuItem::where('id', $child['id'])->update([
                    'parent_id' => $item['id'],
                    'sort_order' => $childOrder,
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }

    public function index()
    {
        $request = request();
        $q = trim($request->get('q', ''));
        $type = $request->get('type', '');
        $status = $request->get('status', '');
        $sortFrom = $request->get('sort_from', '');
        $sortTo = $request->get('sort_to', '');
        $from = $request->get('from', '');
        $to = $request->get('to', '');

        $parents = MenuItem::whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        $menuItems = $this->filterMenuTree($parents, $q, $type, $status, $sortFrom, $sortTo, $from, $to);

        return view('admin.menu-items.index', compact('menuItems', 'q', 'type', 'status', 'sortFrom', 'sortTo', 'from', 'to'));
    }

    public function create(Request $request)
    {
        $parents = MenuItem::whereNull('parent_id')->orderBy('sort_order')->get();
        $defaultType = $request->input('type', 'custom');
        $defaultParentId = $request->input('parent_id');

        return view('admin.menu-items.form', compact('parents', 'defaultType', 'defaultParentId'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('menu-items', 'public');
        }
        MenuItem::create($data);

        return redirect()->route('admin.menu-items.index')->with('success', 'Đã thêm menu item.');
    }

    public function edit(MenuItem $menuItem)
    {
        $parents = MenuItem::whereNull('parent_id')
            ->where('id', '!=', $menuItem->id)
            ->orderBy('sort_order')
            ->get();
        $defaultType = $menuItem->type;

        return view('admin.menu-items.form', compact('menuItem', 'parents', 'defaultType'));
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $data = $this->validated($request, $menuItem->id);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('menu-items', 'public');
        }
        $menuItem->update($data);

        return redirect()->route('admin.menu-items.index')->with('success', 'Đã cập nhật menu item.');
    }

    public function destroy(MenuItem $menuItem)
    {
        // Move children to top-level before deleting parent
        $menuItem->children()->update(['parent_id' => null]);
        $menuItem->delete();

        return redirect()->route('admin.menu-items.index')->with('success', 'Đã xóa menu item.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'label' => 'required|max:150',
            'keyword' => 'nullable|max:500',
            'title' => 'nullable|max:255',
            'description' => 'nullable|string',
            'section_name' => 'nullable|max:150',
            'slug' => 'nullable|max:255|unique:menu_items,slug'.($ignoreId ? ','.$ignoreId : ''),
            'seo_title' => 'nullable|max:70',
            'seo_description' => 'nullable|max:160',
            'type' => 'required|in:custom,page,news_list,product_list,service_list,news_cat,product_cat,service_cat,contact',
            'url' => 'nullable|max:500',
            'target' => 'required|in:_self,_blank',
            'parent_id' => 'nullable|exists:menu_items,id',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['parent_id'] = $request->input('parent_id') ?: null;
        if ($data['type'] !== 'custom') {
            $data['url'] = null;
        }

        return $data;
    }

    private function filterMenuTree(Collection $items, string $q, string $type, string $status, $sortFrom, $sortTo, $from, $to): Collection
    {
        return $items->map(function (MenuItem $item) use ($q, $type, $status, $sortFrom, $sortTo, $from, $to) {
            $children = $this->filterMenuTree($item->children ?? collect(), $q, $type, $status, $sortFrom, $sortTo, $from, $to);
            $item->setRelation('children', $children);

            if ($this->menuMatches($item, $q, $type, $status, $sortFrom, $sortTo, $from, $to) || $children->isNotEmpty()) {
                return $item;
            }

            return null;
        })->filter()->values();
    }

    private function menuMatches(MenuItem $item, string $q, string $type, string $status, $sortFrom, $sortTo, $from, $to): bool
    {
        if ($q !== '') {
            $needle = mb_strtolower($q);
            $haystack = mb_strtolower(implode(' ', array_filter([
                $item->label,
                $item->keyword,
                $item->title,
                $item->description,
                $item->section_name,
                $item->slug,
                $item->url,
                $item->type,
            ])));
            if (! str_contains($haystack, $needle)) {
                return false;
            }
        }

        if ($type !== '' && $item->type !== $type) {
            return false;
        }

        if ($status !== '' && (bool) $item->is_active !== ($status === '1')) {
            return false;
        }

        if ($sortFrom !== '' && (int) $item->sort_order < (int) $sortFrom) {
            return false;
        }

        if ($sortTo !== '' && (int) $item->sort_order > (int) $sortTo) {
            return false;
        }

        if ($from !== '' && optional($item->created_at)->format('Y-m-d') < $from) {
            return false;
        }

        if ($to !== '' && optional($item->created_at)->format('Y-m-d') > $to) {
            return false;
        }

        return true;
    }
}
