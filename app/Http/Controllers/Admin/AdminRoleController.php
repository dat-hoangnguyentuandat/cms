<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use Illuminate\Http\Request;

class AdminRoleController extends Controller
{
    public function index()
    {
        $request = request();
        $q = trim($request->get('q', ''));
        $status = $request->get('status', '');
        $perm = trim($request->get('permission', ''));
        $from = $request->get('from', '');
        $to = $request->get('to', '');

        $query = AdminRole::withCount('adminUsers')->orderBy('name');
        if ($q) {
            $query->where('name', 'like', "%$q%");
        }
        if ($request->filled('status')) {
            $query->where('is_active', $status === '1');
        }
        if ($request->filled('permission')) {
            $query->whereJsonContains('permissions', $perm);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $roles = $query->paginate(20)->appends($request->query());

        return view('admin.admin-roles.index', compact('roles', 'q', 'status', 'perm', 'from', 'to'));
    }

    public function create()
    {
        return view('admin.admin-roles.form');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        AdminRole::create($data);

        return redirect()->route('admin.admin-roles.index')->with('success', 'Đã thêm nhóm admin.');
    }

    public function edit(AdminRole $adminRole)
    {
        return view('admin.admin-roles.form', compact('adminRole'));
    }

    public function update(Request $request, AdminRole $adminRole)
    {
        $data = $this->validated($request, $adminRole->id);
        $adminRole->update($data);

        return redirect()->route('admin.admin-roles.index')->with('success', 'Đã cập nhật nhóm admin.');
    }

    public function destroy(AdminRole $adminRole)
    {
        if ($adminRole->adminUsers()->exists()) {
            return back()->with('error', 'Không thể xóa nhóm đang có admin sử dụng.');
        }
        $adminRole->delete();

        return redirect()->route('admin.admin-roles.index')->with('success', 'Đã xóa nhóm admin.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $request->validate([
            'name' => 'required|max:150|unique:admin_roles,name'.($ignoreId ? ','.$ignoreId : ''),
            'permissions' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        return [
            'name' => $request->input('name'),
            'permissions' => $request->input('permissions', []),
            'is_active' => $request->boolean('is_active', true),
        ];
    }
}
