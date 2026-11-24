<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $catFilter = (int) $request->get('cat', 0);
        $brand = trim($request->get('brand', ''));
        $status = $request->get('status', '');
        $featured = $request->get('featured', '');
        $priceFrom = $request->get('price_from', '');
        $priceTo = $request->get('price_to', '');
        $stockFrom = $request->get('stock_from', '');
        $stockTo = $request->get('stock_to', '');
        $from = $request->get('from', '');
        $to = $request->get('to', '');

        $query = Product::with('category')->orderByDesc('id');
        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%$q%")
                    ->orWhere('sku', 'like', "%$q%")
                    ->orWhere('brand', 'like', "%$q%")
                    ->orWhere('short_desc', 'like', "%$q%");
            });
        }
        if ($catFilter) {
            $query->where('category_id', $catFilter);
        }
        if ($request->filled('brand')) {
            $query->where('brand', 'like', "%$brand%");
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
        if ($request->filled('stock_from')) {
            $query->where('stock', '>=', $stockFrom);
        }
        if ($request->filled('stock_to')) {
            $query->where('stock', '<=', $stockTo);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $total = (clone $query)->count();
        $products = $query->paginate(15)->appends($request->query());
        $categories = Category::with('children')->whereNull('parent_id')
            ->where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.products.index', compact(
            'products', 'categories', 'q', 'catFilter', 'total', 'brand', 'status', 'featured',
            'priceFrom', 'priceTo', 'stockFrom', 'stockTo', 'from', 'to'
        ));
    }

    public function create()
    {
        $categories = Category::with('children')->whereNull('parent_id')
            ->where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.products.form', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['image'] = $this->handleImage($request);
        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Đã thêm sản phẩm.');
    }

    public function edit(Product $product)
    {
        $categories = Category::with('children')->whereNull('parent_id')
            ->where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.products.form', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product->id);
        if ($request->hasFile('image')) {
            $data['image'] = $this->handleImage($request);
        }
        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Đã xóa sản phẩm.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|max:200',
            'slug' => 'nullable|max:220|unique:products,slug'.($ignoreId ? ",$ignoreId" : ''),
            'sku' => 'nullable|max:60',
            'brand' => 'nullable|max:100',
            'short_desc' => 'nullable|max:500',
            'description' => 'nullable',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
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
        $path = $request->file('image')->store('products', 'public');

        return $path;
    }
}
