<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Đăng nhập Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('admin-assets/css/admin.css') }}">
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <div class="login-logo" style="text-align:center;margin-bottom:30px">
      <h1 style="font-size:22px;font-weight:700;text-align:center">Admin Panel</h1>
      <p style="color:#64748b;margin-top:6px;font-size:13px;text-align:center">Đăng nhập để quản trị website</p>
    </div>
    @if($errors->any())
      <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('admin.login.post') }}">
      @csrf
      <div class="form-group">
        <label>Tên đăng nhập</label>
        <input type="text" name="username" class="form-control" value="{{ old('username') }}" required autofocus>
      </div>
      <div class="form-group">
        <label>Mật khẩu</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block" style="margin-top:8px">Đăng nhập</button>
    </form>
    <div style="margin-top:16px;padding:10px 14px;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;font-size:13px;color:#92400e;text-align:center">
      <b>Tài khoản test:</b> admin / admin123
    </div>
  </div>
</div>
</body>
</html>
