@extends('layouts.admin')
@section('title','Cấu hình phân trang')

@section('content')
<div class="wc-layout">
  @include('admin.website-config._sidebar')

  <div class="wc-content">
    <form method="post" action="{{ route('admin.website-config.pagination.update') }}">
      @csrf

      <div class="card">
        <div class="card-header"><h3>Cấu hình phân trang</h3></div>

        <div class="form-row">
          <div class="form-group">
            <label>Số bài viết mỗi trang (danh sách tin tức)</label>
            <input type="number" min="1" max="100" name="settings[news_per_page]" class="form-control" value="{{ $s['news_per_page'] ?? '8' }}">
            <div class="form-help">Số BV hiển thị ở 1 trang menu bài viết</div>
          </div>
          <div class="form-group">
            <label>Số bài viết liên quan</label>
            <input type="number" min="0" max="20" name="settings[news_related_count]" class="form-control" value="{{ $s['news_related_count'] ?? '4' }}">
            <div class="form-help">Số bài viết liên quan hiển thị ở cuối mỗi bài viết</div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Số sản phẩm mỗi trang (danh sách sản phẩm)</label>
            <input type="number" min="1" max="100" name="settings[products_per_page]" class="form-control" value="{{ $s['products_per_page'] ?? '12' }}">
            <div class="form-help">Số SP hiển thị ở 1 trang menu sản phẩm</div>
          </div>
          <div class="form-group">
            <label>Số sản phẩm liên quan</label>
            <input type="number" min="0" max="20" name="settings[products_related_count]" class="form-control" value="{{ $s['products_related_count'] ?? '4' }}">
            <div class="form-help">Số sản phẩm liên quan hiển thị ở cuối mỗi bài sản phẩm</div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Số sản phẩm trong trang tìm kiếm</label>
            <input type="number" min="1" max="100" name="settings[search_per_page]" class="form-control" value="{{ $s['search_per_page'] ?? '12' }}">
            <div class="form-help">Số SP hiển thị tối đa ở 1 trang khi tìm kiếm sản phẩm</div>
          </div>
          <div class="form-group">
            <label>Số dịch vụ mỗi trang</label>
            <input type="number" min="1" max="100" name="settings[services_per_page]" class="form-control" value="{{ $s['services_per_page'] ?? '12' }}">
            <div class="form-help">Số dịch vụ hiển thị ở 1 trang menu dịch vụ</div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Số dịch vụ liên quan</label>
            <input type="number" min="0" max="20" name="settings[services_related_count]" class="form-control" value="{{ $s['services_related_count'] ?? '4' }}">
            <div class="form-help">Số dịch vụ liên quan hiển thị ở cuối mỗi trang dịch vụ</div>
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary">Lưu cấu hình</button>
    </form>
  </div>
</div>
@endsection
