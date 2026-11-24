<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $status = $request->get('status', '');
        $sortFrom = $request->get('sort_from', '');
        $sortTo = $request->get('sort_to', '');
        $from = $request->get('from', '');
        $to = $request->get('to', '');

        $query = Faq::orderBy('sort_order');
        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('question', 'like', "%$q%")
                    ->orWhere('answer', 'like', "%$q%");
            });
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

        $faqs = $query->paginate(20)->appends($request->query());

        return view('admin.faqs.index', compact('faqs', 'q', 'status', 'sortFrom', 'sortTo', 'from', 'to'));
    }

    public function create()
    {
        return view('admin.faqs.form');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Faq::create($data);

        return redirect()->route('admin.faqs.index')->with('success', 'Đã thêm FAQ.');
    }

    public function edit(Faq $faq)
    {
        return view('admin.faqs.form', compact('faq'));
    }

    public function update(Request $request, Faq $faq)
    {
        $faq->update($this->validated($request));

        return redirect()->route('admin.faqs.index')->with('success', 'Đã cập nhật.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')->with('success', 'Đã xóa.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'question' => 'required|max:300',
            'answer' => 'required',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
