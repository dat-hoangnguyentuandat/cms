@extends('layouts.admin')
@section('title','Đổi mật khẩu')

@section('content')
<div class="card">
  <div class="card-header">
    <h3>Đổi mật khẩu</h3>
  </div>

  <form method="post" action="{{ route('admin.password.update') }}">
    @csrf

    <div class="form-row">
      <div class="form-group">
        <label>Mật khẩu hiện tại <span class="req">*</span></label>
        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password">
        @error('current_password')<div class="form-error">{{ $message }}</div>@enderror
      </div>
      <div></div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Mật khẩu mới <span class="req">*</span></label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
        @error('password')<div class="form-error">{{ $message }}</div>@enderror
      </div>
      <div class="form-group">
        <label>Xác nhận mật khẩu mới <span class="req">*</span></label>
        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Cập nhật mật khẩu</button>
    </div>
  </form>
</div>
@endsection
