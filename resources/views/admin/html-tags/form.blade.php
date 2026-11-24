@extends('layouts.admin')
@section('title', isset($htmlTag) ? 'Sửa HTML Tag' : 'Thêm HTML Tag')

@section('content')
<div class="card">
  <div class="card-header">
    <h3>{{ isset($htmlTag) ? 'Sửa' : 'Thêm' }} HTML Tag</h3>
    <a href="{{ route('admin.html-tags.index') }}" class="btn btn-outline btn-sm">← Quay lại</a>
  </div>

  <div class="alert" style="background:var(--warning-bg,#fffbeb);border:1px solid var(--warning-border,#fcd34d);color:#92400e;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px">
    <b>⚠ Cảnh báo bảo mật:</b> Nội dung HTML/JS được nhúng trực tiếp vào trang mà không qua escape.
    Chỉ thêm script từ nguồn tin cậy (Google, Facebook, Zalo...). Không nhập code từ nguồn không rõ ràng.
  </div>

  <form method="post"
        action="{{ isset($htmlTag) ? route('admin.html-tags.update', $htmlTag) : route('admin.html-tags.store') }}">
    @csrf
    @if(isset($htmlTag)) @method('PUT') @endif

    @if($errors->any())
      <div class="alert alert-error" style="margin-bottom:16px">
        <ul style="margin:0;padding-left:18px">
          @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
      </div>
    @endif

    <div class="form-row">
      <div class="form-group">
        <label>Tên tag <span class="req">*</span></label>
        <input type="text" name="name" class="form-control" placeholder="VD: Google Tag Manager"
               value="{{ old('name', $htmlTag->name ?? '') }}" required>
      </div>
      <div class="form-group">
        <label>Vị trí <span class="req">*</span></label>
        <select name="position" class="form-control" required>
          <option value="">-- Chọn vị trí --</option>
          <option value="head"       {{ old('position', $htmlTag->position ?? '') === 'head'       ? 'selected' : '' }}>Trong &lt;head&gt;</option>
          <option value="body_start" {{ old('position', $htmlTag->position ?? '') === 'body_start' ? 'selected' : '' }}>Ngay sau &lt;body&gt;</option>
          <option value="body_end"   {{ old('position', $htmlTag->position ?? '') === 'body_end'   ? 'selected' : '' }}>Trước &lt;/body&gt;</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label>Mô tả</label>
      <input type="text" name="description" class="form-control" placeholder="VD: Tracking chuyển đổi Google Ads"
             value="{{ old('description', $htmlTag->description ?? '') }}">
    </div>

    <div class="form-group">
      <label>Nội dung HTML / JavaScript <span class="req">*</span></label>
      <textarea name="content" id="tag-content" class="form-control"
                rows="12" required
                style="font-family:monospace;font-size:13px;resize:vertical"
                placeholder="Dán đoạn script hoặc HTML vào đây...">{{ old('content', $htmlTag->content ?? '') }}</textarea>
      <div class="form-help">Dán nguyên đoạn code từ Google Tag Manager, GA4, Facebook Pixel, Zalo OA, v.v.</div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Thứ tự</label>
        <input type="number" name="sort_order" class="form-control"
               value="{{ old('sort_order', $htmlTag->sort_order ?? 0) }}">
        <div class="form-help">Số nhỏ hơn render trước.</div>
      </div>
      <div style="display:flex;flex-direction:column;gap:12px;justify-content:flex-end;padding-bottom:4px">
        <label class="form-check">
          <input type="checkbox" name="is_active" value="1"
                 {{ old('is_active', $htmlTag->is_active ?? 1) ? 'checked' : '' }}>
          Kích hoạt
        </label>
        <label class="form-check">
          <input type="checkbox" name="only_homepage" value="1"
                 {{ old('only_homepage', $htmlTag->only_homepage ?? 0) ? 'checked' : '' }}>
          Chỉ hiển thị ở trang chủ
        </label>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Lưu</button>
  </form>
</div>
@endsection
