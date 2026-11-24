<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return view('contact');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|min:2|max:150',
            'phone' => ['required', 'regex:/^(0|\+84)[0-9]{9}$/'],
            'email' => 'nullable|email|max:150',
            'subject' => 'nullable|max:200',
            'message' => 'required|min:10',
        ]);

        $data = cms_apply_hook('cms.contact.before_store', $data, $request);
        $contact = Contact::create($data);
        cms_do_hook('cms.contact.after_store', $contact, $request);

        return response()->json(['success' => true, 'message' => 'Gửi thành công!']);
    }
}
