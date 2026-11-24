<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $status = $request->get('status', '');
        $from = $request->get('from', '');
        $to = $request->get('to', '');

        $query = Contact::orderByDesc('id');
        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%$q%")
                    ->orWhere('phone', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%")
                    ->orWhere('subject', 'like', "%$q%")
                    ->orWhere('message', 'like', "%$q%");
            });
        }
        if ($request->filled('status')) {
            $query->where('is_read', $status === '1');
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $total = (clone $query)->count();
        $contacts = $query->paginate(20)->appends($request->query());

        return view('admin.contacts.index', compact('contacts', 'total', 'q', 'status', 'from', 'to'));
    }

    public function show(Contact $contact)
    {
        $contact->update(['is_read' => true]);

        return view('admin.contacts.show', compact('contact'));
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('admin.contacts.index')->with('success', 'Đã xóa liên hệ.');
    }
}
