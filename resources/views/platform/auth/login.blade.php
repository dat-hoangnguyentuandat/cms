<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Đăng nhập | Platform Admin</title>
    <link rel="stylesheet" href="{{ asset('admin-assets/css/platform.css') }}">
</head>
<body class="login-page">
    <main class="login-panel">
        <div class="login-heading">
            <span class="brand-mark">P</span>
            <div>
                <h1>Platform Admin</h1>
                <p>Đăng nhập quản trị tenant</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('platform.login.post') }}" class="stack">
            @csrf
            <label>
                Tài khoản
                <input name="username" value="{{ old('username') }}" autocomplete="username" required autofocus>
            </label>
            <label>
                Mật khẩu
                <input type="password" name="password" autocomplete="current-password" required>
            </label>
            <button class="button primary full" type="submit">Đăng nhập</button>
        </form>
    </main>
</body>
</html>
