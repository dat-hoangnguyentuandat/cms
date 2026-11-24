<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $parentId = (int) $request->get('parent_id', 0);
        $status = $request->get('status', '');
        $sortFrom = $request->get('sort_from', '');
        $sortTo = $request->get('sort_to', '');
        $from = $request->get('from', '');
        $to = $request->get('to', '');

        $query = Category::with('parent')->withCount('products')->orderBy('parent_id')->orderBy('sort_order');
        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%$q%")
                    ->orWhere('slug', 'like', "%$q%")
                    ->orWhere('description', 'like', "%$q%");
            });
        }
        if ($parentId) {
            $query->where('parent_id', $parentId);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $status === '1');
        }
        if ($request->filled('sort_from')) {
            $query->where('sort_order', '>=', $sortFrom);
        }
        if ($request->filled('sort_to')) {
            $query->where('sort_order', '<=', $sortTo);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $categories = $query->paginate(20)->appends($request->query());
        $parents = Category::whereNull('parent_id')->orderBy('sort_order')->get();

        return view('admin.categories.index', compact('categories', 'parents', 'q', 'parentId', 'status', 'sortFrom', 'sortTo', 'from', 'to'));
    }

    public function create()
    {
        $parents = Category::whereNull('parent_id')->orderBy('sort_order')->get();

        return view('admin.categories.form', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }
        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Đã thêm danh mục.');
    }

    public function edit(Category $category)
    {
        $parents = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('sort_order')->get();

        return view('admin.categories.form', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validated($request, $category->id);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }
        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Đã cập nhật danh mục.');
    }

    public function destroy(Category $category)
    {
        // Move children to top-level before deleting parent
        $category->children()->update(['parent_id' => null]);
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Đã xóa danh mục.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => 'required|max:150',
            'slug' => 'nullable|max:180|unique:categories,slug'.($ignoreId ? ",$ignoreId" : ''),
            'description' => 'nullable',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);
        $data['slug'] = $data['slug'] ?: Str::slug($request->name);
        $data['is_active'] = $request->boolean('is_active');
        $data['parent_id'] = $request->input('parent_id') ?: null;

        return $data;
    }
}
