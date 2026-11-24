<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return session('admin_id') ? redirect()->route('admin.dashboard') : view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = AdminUser::where('username', $request->username)->where('is_active', true)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['username' => 'Tên đăng nhập hoặc mật khẩu không đúng.'])->withInput();
        }

        $user->update(['last_login' => now()]);
        session(['admin_id' => $user->id, 'admin_user' => $user->toArray()]);

        return redirect()->route('admin.dashboard');
    }

    public function logout()
    {
        session()->forget(['admin_id', 'admin_user']);

        return redirect()->route('admin.login');
    }

    public function showChangePassword()
    {
        return view('admin.profile.password');
    }

    public function showProfile()
    {
        $user = AdminUser::findOrFail(session('admin_id'));

        return view('admin.profile.index', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = AdminUser::findOrFail(session('admin_id'));

        $request->validate([
            'full_name' => 'required|max:100',
            'email' => 'required|email|max:150',
        ], [
            'full_name.required' => 'Vui lòng nhập tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
        ]);

        $user->update([
            'full_name' => $request->input('full_name'),
            'email' => $request->input('email'),
        ]);

        session(['admin_user' => $user->fresh()->toArray()]);

        return back()->with('success', 'Đã cập nhật thông tin tài khoản.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $user = AdminUser::findOrFail(session('admin_id'));

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Đã đổi mật khẩu thành công.');
    }
}
