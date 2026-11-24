<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('platform_admin_id')) {
            return redirect()->route('platform.tenants.index');
        }

        return view('platform.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ]);

        $admin = PlatformAdmin::query()
            ->where('username', strtolower($credentials['username']))
            ->where('is_active', true)
            ->first();

        if (! $admin || ! Hash::check($credentials['password'], $admin->password)) {
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => 'Tài khoản hoặc mật khẩu không chính xác.']);
        }

        if (Hash::needsRehash($admin->password)) {
            $admin->update(['password' => $credentials['password']]);
        }
        $admin->update(['last_login_at' => now()]);

        $request->session()->regenerate();
        $request->session()->put('platform_admin_id', $admin->id);

        return redirect()->intended(route('platform.tenants.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('platform.login');
    }
}
