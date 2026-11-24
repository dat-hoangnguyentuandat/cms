@extends('layouts.admin')
@section('title','Thông tin tài khoản')

@section('content')
<div class="card">
  <div class="card-header">
    <h3>Thông tin tài khoản</h3>
  </div>
  <form method="post" action="{{ route('admin.profile.update') }}">
    @csrf
    <div class="form-row">
      <div class="form-group">
        <label>Tên hiển thị <span class="req">*</span></label>
        <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $user->full_name) }}" required>
        @error('full_name')<div class="form-error">{{ $message }}</div>@enderror
      </div>
      <div class="form-group">
        <label>Tên đăng nhập</label>
        <input type="text" class="form-control" value="{{ $user->username }}" disabled style="background:#f8fafc;color:#6b7280">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Email <span class="req">*</span></label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
        @error('email')<div class="form-error">{{ $message }}</div>@enderror
      </div>
      <div class="form-group">
        <label>Vai trò</label>
        <div style="padding:10px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-size:14px">
          <span class="badge badge-primary">Super Admin</span>
        </div>
      </div>
    </div>
    <div class="form-group">
      <label>Đăng nhập lần cuối</label>
      <div style="padding:10px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;color:#6b7280">
        {{ $user->last_login ? $user->last_login->format('d/m/Y H:i') : '—' }}
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Cập nhật</button>
    </div>
  </form>
</div>
@endsection
