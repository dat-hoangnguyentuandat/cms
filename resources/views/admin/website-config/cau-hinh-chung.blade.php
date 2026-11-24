@extends('layouts.admin')
@section('title','Cấu hình chung')

@section('content')
<div class="wc-layout">
  @include('admin.website-config._sidebar')

  <div class="wc-content">
    <form method="post" action="{{ route('admin.website-config.general.update') }}">
      @csrf

      <div class="card">
        <div class="card-header"><h3>Trang chủ</h3></div>
        <div class="form-group">
          <label>Tiêu đề section "Giới thiệu"</label>
          <input type="text" name="settings[about_title]" class="form-control" value="{{ $s['about_title'] ?? '' }}">
        </div>
        <div class="form-group">
          <label>Mô tả section "Giới thiệu"</label>
          <textarea name="settings[about_desc]" class="form-control rich-editor" rows="4">{{ $s['about_desc'] ?? '' }}</textarea>
        </div>
      </div>

      <button type="submit" class="btn btn-primary">Lưu cấu hình</button>
    </form>
  </div>
</div>
@endsection
