@extends('layouts.admin')
@section('title','Cài đặt')

@section('content')
<form method="post" action="{{ route('admin.settings.update') }}">
  @csrf

  <div class="card">
    <div class="card-header"><h3>Thông tin chung</h3></div>
    <div class="form-row">
      <div class="form-group">
        <label>Tên website</label>
        <input type="text" name="settings[site_name]" class="form-control" value="{{ $s['site_name'] ?? '' }}">
      </div>
      <div class="form-group">
        <label>Email liên hệ</label>
        <input type="email" name="settings[email]" class="form-control" value="{{ $s['email'] ?? '' }}">
      </div>
    </div>
    <div class="form-group">
      <label>Mô tả website</label>
      <textarea name="settings[site_desc]" class="form-control rich-editor" rows="3">{{ $s['site_desc'] ?? '' }}</textarea>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3>Liên hệ</h3></div>
    <div class="form-row-3">
      <div class="form-group">
        <label>Hotline</label>
        <input type="text" name="settings[hotline]" class="form-control" value="{{ $s['hotline'] ?? '' }}">
      </div>
      <div class="form-group">
        <label>Số Zalo</label>
        <input type="text" name="settings[zalo]" class="form-control" value="{{ $s['zalo'] ?? '' }}">
      </div>
      <div class="form-group">
        <label>Facebook URL</label>
        <input type="text" name="settings[facebook_url]" class="form-control" value="{{ $s['facebook_url'] ?? '' }}">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Địa chỉ</label>
        <input type="text" name="settings[address]" class="form-control" value="{{ $s['address'] ?? '' }}">
      </div>
      <div class="form-group">
        <label>Giờ làm việc</label>
        <input type="text" name="settings[working_hours]" class="form-control" value="{{ $s['working_hours'] ?? '' }}">
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3>Trang chủ</h3></div>
    <div class="form-group">
      <label>Tiêu đề section "Giới thiệu"</label>
      <input type="text" name="settings[about_title]" class="form-control" value="{{ $s['about_title'] ?? '' }}">
    </div>
    <div class="form-group">
      <label>Mô tả section "Giới thiệu"</label>
      <textarea name="settings[about_desc]" class="form-control rich-editor" rows="3">{{ $s['about_desc'] ?? '' }}</textarea>
    </div>
  </div>

  <button type="submit" class="btn btn-primary">Lưu tất cả thay đổi</button>
</form>
@endsection
