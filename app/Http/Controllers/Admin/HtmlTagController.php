<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HtmlTag;
use Illuminate\Http\Request;

class HtmlTagController extends Controller
{
    public function index()
    {
        $request = request();
        $q = trim($request->get('q', ''));
        $position = $request->get('position', '');
        $status = $request->get('status', '');
        $homeOnly = $request->get('home_only', '');
        $sortFrom = $request->get('sort_from', '');
        $sortTo = $request->get('sort_to', '');
        $from = $request->get('from', '');
        $to = $request->get('to', '');

        $query = HtmlTag::orderBy('sort_order')->orderBy('id');
        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%$q%")
                    ->orWhere('description', 'like', "%$q%")
                    ->orWhere('content', 'like', "%$q%");
            });
        }
        if ($request->filled('position')) {
            $query->where('position', $position);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $status === '1');
        }
        if ($request->filled('home_only')) {
            $query->where('only_homepage', $homeOnly === '1');
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

        $tags = $query->paginate(20)->appends($request->query());

        return view('admin.html-tags.index', compact('tags', 'q', 'position', 'status', 'homeOnly', 'sortFrom', 'sortTo', 'from', 'to'));
    }

    public function create()
    {
        return view('admin.html-tags.form');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        HtmlTag::create($data);

        return redirect()->route('admin.html-tags.index')->with('success', 'Đã thêm HTML tag.');
    }

    public function edit(HtmlTag $htmlTag)
    {
        return view('admin.html-tags.form', compact('htmlTag'));
    }

    public function update(Request $request, HtmlTag $htmlTag)
    {
        $data = $this->validated($request);
        $htmlTag->update($data);

        return redirect()->route('admin.html-tags.index')->with('success', 'Đã cập nhật HTML tag.');
    }

    public function destroy(HtmlTag $htmlTag)
    {
        $htmlTag->delete();

        return redirect()->route('admin.html-tags.index')->with('success', 'Đã xóa HTML tag.');
    }

    public function toggle(HtmlTag $htmlTag)
    {
        $htmlTag->update(['is_active' => ! $htmlTag->is_active]);

        return redirect()->route('admin.html-tags.index')
            ->with('success', $htmlTag->is_active ? 'Đã bật HTML tag.' : 'Đã tắt HTML tag.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|max:150',
            'position' => 'required|in:head,body_start,body_end',
            'description' => 'nullable|max:255',
            'content' => 'required',
            'is_active' => 'boolean',
            'only_homepage' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['only_homepage'] = $request->boolean('only_homepage');

        return $data;
    }
}
