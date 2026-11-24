@extends('layouts.admin')
@section('title', isset($adminUser) ? 'Sửa tài khoản admin' : 'Thêm tài khoản admin')

@section('content')
<div class="card">
  <div class="card-header">
    <h3>{{ isset($adminUser) ? 'Sửa tài khoản admin' : 'Thêm tài khoản admin' }}</h3>
    <a href="{{ route('admin.admin-users.index') }}" class="btn btn-outline btn-sm">← Quay lại</a>
  </div>

  @if($roles->isEmpty() && !isset($adminUser))
  <div class="alert alert-error" style="margin:24px">
    <b>Bạn chưa có nhóm admin nào!</b> Vui lòng tạo nhóm <a href="{{ route('admin.admin-roles.create') }}" style="color:inherit;text-decoration:underline;font-weight:600">tại đây</a>.
  </div>
  @else
  <form method="post" action="{{ isset($adminUser) ? route('admin.admin-users.update', $adminUser) : route('admin.admin-users.store') }}">
    @csrf
    @if(isset($adminUser)) @method('PUT') @endif

    <div class="form-row">
      <div class="form-group">
        <label>Tên Admin <span class="req">*</span></label>
        <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $adminUser->full_name ?? '') }}" required placeholder="VD: Nguyễn Văn A">
        @error('full_name')<div class="form-error">{{ $message }}</div>@enderror
      </div>
      <div class="form-group">
        <label>Tên đăng nhập <span class="req">*</span></label>
        <input type="text" name="username" class="form-control" value="{{ old('username', $adminUser->username ?? '') }}" required placeholder="VD: ctv1">
        @error('username')<div class="form-error">{{ $message }}</div>@enderror
      </div>
    </div>

    <div class="form-group">
      <label>Email <span class="req">*</span></label>
      <input type="email" name="email" class="form-control" value="{{ old('email', $adminUser->email ?? '') }}" required placeholder="VD: ctv1@company.vn">
      <div style="font-size:12px;color:#6b7280;margin-top:4px">Dùng để đăng nhập và khôi phục mật khẩu.</div>
      @error('email')<div class="form-error">{{ $message }}</div>@enderror
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Mật khẩu {!! isset($adminUser) ? '' : '<span class="req">*</span>' !!}</label>
        <input type="password" name="password" class="form-control" placeholder="{{ isset($adminUser) ? 'Để trống nếu không đổi' : '' }}">
        @error('password')<div class="form-error">{{ $message }}</div>@enderror
      </div>
      <div class="form-group">
        <label>Nhập lại mật khẩu</label>
        <input type="password" name="password_confirmation" class="form-control">
      </div>
    </div>

    <div class="form-group">
      <label>Chọn nhóm admin <span class="req">*</span></label>
      <select name="role_id" class="form-control">
        @foreach($roles as $role)
          <option value="{{ $role->id }}" {{ old('role_id', $adminUser->role_id ?? '') == $role->id ? 'selected' : '' }}>
            {{ $role->name }}
          </option>
        @endforeach
      </select>
      @error('role_id')<div class="form-error">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:400">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" style="width:16px;height:16px"
          {{ old('is_active', $adminUser->is_active ?? true) ? 'checked' : '' }}>
        <span>Kích hoạt tài khoản</span>
      </label>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">{{ isset($adminUser) ? 'Cập nhật' : 'Lưu' }}</button>
    </div>
  </form>
  @endif
</div>
@endsection
