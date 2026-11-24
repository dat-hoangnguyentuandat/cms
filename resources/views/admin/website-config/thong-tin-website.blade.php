@extends('layouts.admin')
@section('title','Thông tin website')

@section('content')
<div class="wc-layout">
  @include('admin.website-config._sidebar')

  <div class="wc-content">
    <form method="post" action="{{ route('admin.website-config.info.update') }}" enctype="multipart/form-data">
      @csrf

      <div class="card">
        <div class="card-header"><h3>Thông tin cơ bản</h3></div>
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
          <textarea name="settings[site_desc]" class="form-control" rows="4">{{ $s['site_desc'] ?? '' }}</textarea>
        </div>
        <div class="form-group">
          <label>Logo website</label>
          @if(!empty($s['site_logo']))
            <div style="margin-bottom: 10px;">
              <img src="{{ asset('storage/' . $s['site_logo']) }}" alt="Logo" style="max-width: 200px; max-height: 100px; border: 1px solid #ddd; padding: 5px;">
            </div>
          @endif
          <input type="file" name="site_logo" class="form-control" accept="image/*">
          <small class="form-text text-muted">Định dạng: JPG, PNG, GIF. Kích thước khuyến nghị: 200x100px</small>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>Thông tin liên hệ</h3></div>
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

      <button type="submit" class="btn btn-primary">Lưu thông tin</button>
    </form>
  </div>
</div>
@endsection
