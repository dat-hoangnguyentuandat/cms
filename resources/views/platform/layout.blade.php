<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', 'Tenant') | Platform Admin</title>
    <link rel="stylesheet" href="{{ asset('admin-assets/css/platform.css') }}">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('platform.tenants.index') }}">
            <span>Platform Admin</span>
        </a>
        <nav class="topnav" aria-label="Điều hướng">
            <a href="{{ route('platform.tenants.index') }}" @class(['active' => request()->routeIs('platform.tenants.*')])>Tenants</a>
            <a href="{{ route('platform.catalog.index') }}" @class(['active' => request()->routeIs('platform.catalog.*')])>Catalog</a>
            <a href="{{ route('platform.admins.index') }}" @class(['active' => request()->routeIs('platform.admins.*')])>Platform Admins</a>
        </nav>
        <div class="account">
            <span>{{ request()->attributes->get('platformAdmin')?->name }}</span>
            <form method="POST" action="{{ route('platform.logout') }}">
                @csrf
                <button class="icon-button" type="submit" title="Đăng xuất" aria-label="Đăng xuất">↪</button>
            </form>
        </div>
    </header>

    <main class="page">
        @if (session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert error">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert error">
                <strong>Không thể xử lý yêu cầu.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
