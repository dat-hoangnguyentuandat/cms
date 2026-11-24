<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index()
    {
        $request = request();
        $q = trim($request->get('q', ''));
        $group = $request->get('group', '');
        $status = $request->get('status', '');
        $from = $request->get('from', '');
        $to = $request->get('to', '');
        $loginFrom = $request->get('login_from', '');
        $loginTo = $request->get('login_to', '');

        $query = AdminUser::with('adminRole')->orderBy('full_name');
        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('full_name', 'like', "%$q%")
                    ->orWhere('username', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%");
            });
        }
        if ($group === 'admin') {
            $query->where('role', 'admin');
        } elseif ($group !== '') {
            $query->where('role_id', (int) $group);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $status === '1');
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $to);
        }
        if ($request->filled('login_from')) {
            $query->whereDate('last_login', '>=', $loginFrom);
        }
        if ($request->filled('login_to')) {
            $query->whereDate('last_login', '<=', $loginTo);
        }

        $adminUsers = $query->paginate(20)->appends($request->query());
        $roles = AdminRole::where('is_active', true)->orderBy('name')->get();

        return view('admin.admin-users.index', compact('adminUsers', 'roles', 'q', 'group', 'status', 'from', 'to', 'loginFrom', 'loginTo'));
    }

    public function create()
    {
        $roles = AdminRole::where('is_active', true)->orderBy('name')->get();

        return view('admin.admin-users.form', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|max:100',
            'username' => 'required|max:50|unique:admin_users,username',
            'email' => 'required|email|max:150',
            'password' => 'required|min:6|confirmed',
            'role_id' => 'required|exists:admin_roles,id',
            'is_active' => 'boolean',
        ], $this->messages());

        AdminUser::create([
            'full_name' => $request->input('full_name'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role_id' => $request->input('role_id') ?: null,
            'role' => 'editor',
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.admin-users.index')->with('success', 'Đã thêm tài khoản admin.');
    }

    public function edit(AdminUser $adminUser)
    {
        $roles = AdminRole::where('is_active', true)->orderBy('name')->get();

        return view('admin.admin-users.form', compact('adminUser', 'roles'));
    }

    public function update(Request $request, AdminUser $adminUser)
    {
        $request->validate([
            'full_name' => 'required|max:100',
            'username' => 'required|max:50|unique:admin_users,username,'.$adminUser->id,
            'email' => 'required|email|max:150',
            'password' => 'nullable|min:6|confirmed',
            'role_id' => 'nullable|exists:admin_roles,id',
            'is_active' => 'boolean',
        ], $this->messages());

        $data = [
            'full_name' => $request->input('full_name'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'role_id' => $request->input('role_id') ?: null,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $adminUser->update($data);

        // Refresh session if editing self
        if (session('admin_id') == $adminUser->id) {
            session(['admin_user' => $adminUser->fresh()->toArray()]);
        }

        return redirect()->route('admin.admin-users.index')->with('success', 'Đã cập nhật tài khoản admin.');
    }

    public function destroy(AdminUser $adminUser)
    {
        if (session('admin_id') == $adminUser->id) {
            return back()->with('error', 'Không thể xóa tài khoản đang đăng nhập.');
        }
        $adminUser->delete();

        return redirect()->route('admin.admin-users.index')->with('success', 'Đã xóa tài khoản admin.');
    }

    private function messages(): array
    {
        return [
            'full_name.required' => 'Vui lòng nhập tên admin.',
            'username.required' => 'Vui lòng nhập tên đăng nhập.',
            'username.unique' => 'Tên đăng nhập đã tồn tại.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'role_id.required' => 'Vui lòng chọn nhóm admin.',
            'role_id.exists' => 'Nhóm admin không hợp lệ.',
        ];
    }
}
