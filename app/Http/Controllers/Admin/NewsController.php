<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $cat = (int) $request->get('cat', 0);
        $status = $request->get('status', '');
        $author = trim($request->get('author', ''));
        $viewsMin = $request->get('views_min', '');
        $viewsMax = $request->get('views_max', '');
        $from = $request->get('from', '');
        $to = $request->get('to', '');

        $query = News::with('category')->orderByDesc('id');
        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('title', 'like', "%$q%")
                    ->orWhere('slug', 'like', "%$q%")
                    ->orWhere('excerpt', 'like', "%$q%")
                    ->orWhere('content', 'like', "%$q%");
            });
        }
        if ($cat) {
            $query->where('category_id', $cat);
        }
        if ($request->filled('status')) {
            $query->where('is_published', $status === '1');
        }
        if ($request->filled('author')) {
            $query->where('author', 'like', "%$author%");
        }
        if ($request->filled('views_min')) {
            $query->where('views', '>=', $viewsMin);
        }
        if ($request->filled('views_max')) {
            $query->where('views', '<=', $viewsMax);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $total = (clone $query)->count();
        $posts = $query->paginate(15)->appends($request->query());

        return view('admin.news.index', compact('posts', 'total', 'q', 'cat', 'status', 'author', 'viewsMin', 'viewsMax', 'from', 'to'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.news.form', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('news', 'public');
        }
        News::create($data);

        return redirect()->route('admin.news.index')->with('success', 'Đã thêm bài viết.');
    }

    public function edit(News $news)
    {
        $post = $news;
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.news.form', compact('post', 'categories'));
    }

    public function update(Request $request, News $news)
    {
        $data = $this->validated($request, $news->id);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('news', 'public');
        }
        $news->update($data);

        return redirect()->route('admin.news.index')->with('success', 'Đã cập nhật bài viết.');
    }

    public function destroy(News $news)
    {
        $news->delete();

        return redirect()->route('admin.news.index')->with('success', 'Đã xóa bài viết.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $rules = [
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'required|max:220',
            'slug' => 'nullable|max:240|unique:news,slug'.($ignoreId ? ",$ignoreId" : ''),
            'excerpt' => 'nullable|max:500',
            'content' => 'nullable',
            'author' => 'nullable|max:100',
            'is_published' => 'boolean',
        ];
        $data = $request->validate($rules);
        $data['slug'] = $data['slug'] ?: Str::slug($request->title);
        $data['is_published'] = $request->boolean('is_published');
        $data['category_id'] = $request->input('category_id') ?: null;

        return $data;
    }
}
