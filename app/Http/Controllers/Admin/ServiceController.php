<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $catFilter = (int) $request->get('cat', 0);
        $status = $request->get('status', '');
        $featured = $request->get('featured', '');
        $priceFrom = $request->get('price_from', '');
        $priceTo = $request->get('price_to', '');
        $duration = trim($request->get('duration', ''));
        $from = $request->get('from', '');
        $to = $request->get('to', '');

        $query = Service::with('category')->orderByDesc('id');
        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%$q%")
                    ->orWhere('short_desc', 'like', "%$q%")
                    ->orWhere('description', 'like', "%$q%");
            });
        }
        if ($catFilter) {
            $query->where('category_id', $catFilter);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $status === '1');
        }
        if ($request->filled('featured')) {
            $query->where('is_featured', $featured === '1');
        }
        if ($request->filled('price_from')) {
            $query->where('price', '>=', $priceFrom);
        }
        if ($request->filled('price_to')) {
            $query->where('price', '<=', $priceTo);
        }
        if ($request->filled('duration')) {
            $query->where('duration', 'like', "%$duration%");
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $total = (clone $query)->count();
        $services = $query->paginate(15)->appends($request->query());
        $categories = Category::with('children')->whereNull('parent_id')
            ->where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.services.index', compact(
            'services', 'categories', 'q', 'catFilter', 'total', 'status', 'featured',
            'priceFrom', 'priceTo', 'duration', 'from', 'to'
        ));
    }

    public function create()
    {
        $categories = Category::with('children')->whereNull('parent_id')
            ->where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.services.form', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['image'] = $this->handleImage($request);
        Service::create($data);

        return redirect()->route('admin.services.index')->with('success', 'Đã thêm dịch vụ.');
    }

    public function edit(Service $service)
    {
        $categories = Category::with('children')->whereNull('parent_id')
            ->where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.services.form', compact('service', 'categories'));
    }

    public function update(Request $request, Service $service)
    {
        $data = $this->validated($request, $service->id);
        if ($request->hasFile('image')) {
            $data['image'] = $this->handleImage($request);
        }
        $service->update($data);

        return redirect()->route('admin.services.index')->with('success', 'Đã cập nhật dịch vụ.');
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('admin.services.index')->with('success', 'Đã xóa dịch vụ.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|max:200',
            'slug' => 'nullable|max:220|unique:services,slug'.($ignoreId ? ",$ignoreId" : ''),
            'short_desc' => 'nullable|max:500',
            'description' => 'nullable',
            'price' => 'required|numeric|min:0',
            'duration' => 'nullable|max:60',
        ]);
        $data['slug'] = $data['slug'] ?: Str::slug($request->name);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function handleImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        return $request->file('image')->store('services', 'public');
    }
}
