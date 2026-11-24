@extends('layouts.admin')
@section('title', isset($category) ? 'Sửa danh mục' : 'Thêm danh mục')

@section('content')
<div class="card">
  <div class="card-header">
    <h3>{{ isset($category) ? 'Sửa' : 'Thêm' }} danh mục</h3>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline btn-sm">← Quay lại</a>
  </div>

  <form method="post"
        action="{{ isset($category) ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
        enctype="multipart/form-data">
    @csrf
    @if(isset($category)) @method('PUT') @endif

    <div class="form-row">
      <div class="form-group">
        <label>Tên danh mục <span class="req">*</span></label>
        <input type="text" name="name" id="cat-name" class="form-control" value="{{ old('name', $category->name ?? '') }}" required>
      </div>
      <div class="form-group">
        <label>Slug (URL)</label>
        <input type="text" name="slug" id="cat-slug" class="form-control" value="{{ old('slug', $category->slug ?? '') }}">
        <div class="form-help">Để trống sẽ tự sinh từ tên.</div>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Danh mục cha</label>
        <select name="parent_id" class="form-control">
          <option value="">-- Không có (danh mục gốc) --</option>
          @foreach($parents as $p)
            <option value="{{ $p->id }}" {{ old('parent_id', $category->parent_id ?? '') == $p->id ? 'selected' : '' }}>
              {{ $p->name }}
            </option>
          @endforeach
        </select>
        <div class="form-help">Chọn danh mục cha nếu đây là danh mục con.</div>
      </div>
      <div class="form-group">
        <label>Mô tả</label>
        <textarea name="description" class="form-control rich-editor">{{ old('description', $category->description ?? '') }}</textarea>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Ảnh đại diện</label>
        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImg(this,'cat-img')">
        @if(isset($category) && $category->image)
          <img id="cat-img" class="img-preview" src="{{ $category->image_url }}" alt="">
        @else
          <img id="cat-img" class="img-preview" style="display:none" alt="">
        @endif
      </div>
      <div>
        <div class="form-group">
          <label>Thứ tự</label>
          <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
        </div>
        <div class="form-group">
          <label class="form-check">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active ?? 1) ? 'checked' : '' }}>
            Kích hoạt
          </label>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Lưu danh mục</button>
  </form>
</div>
@endsection

@push('js')
<script>
function previewImg(input, id) {
  const img = document.getElementById(id);
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => { img.src = e.target.result; img.style.display = ''; };
    reader.readAsDataURL(input.files[0]);
  }
}
document.getElementById('cat-name')?.addEventListener('input', function() {
  const slug = document.getElementById('cat-slug');
  if (!slug.dataset.manual) {
    slug.value = this.value.toLowerCase()
      .normalize('NFD').replace(/[̀-ͯ]/g,'')
      .replace(/đ/g,'d').replace(/[^a-z0-9\s-]/g,'')
      .trim().replace(/\s+/g,'-');
  }
});
document.getElementById('cat-slug')?.addEventListener('input', function() {
  this.dataset.manual = '1';
});
</script>
@endpush
