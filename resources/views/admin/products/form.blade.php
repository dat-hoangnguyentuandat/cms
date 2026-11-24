@extends('layouts.admin')
@section('title', isset($product) ? 'Sửa sản phẩm' : 'Thêm sản phẩm')

@section('content')
<div class="card">
  <div class="card-header">
    <h3>{{ isset($product) ? 'Sửa' : 'Thêm' }} sản phẩm</h3>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline btn-sm">← Quay lại</a>
  </div>

  <form method="post"
        action="{{ isset($product) ? route('admin.products.update', $product) : route('admin.products.store') }}"
        enctype="multipart/form-data">
    @csrf
    @if(isset($product)) @method('PUT') @endif

    <div class="form-row">
      <div class="form-group">
        <label>Tên sản phẩm <span class="req">*</span></label>
        <input type="text" name="name" id="p-name" class="form-control" value="{{ old('name', $product->name ?? '') }}" required>
      </div>
      <div class="form-group">
        <label>Slug</label>
        <input type="text" name="slug" id="p-slug" class="form-control" value="{{ old('slug', $product->slug ?? '') }}">
      </div>
    </div>

    <div class="form-row-3">
      <div class="form-group">
        <label>Danh mục</label>
        <select name="category_id" class="form-control">
          <option value="">-- Chọn danh mục --</option>
          @foreach($categories as $parent)
            <option value="{{ $parent->id }}" {{ old('category_id', $product->category_id ?? '') == $parent->id ? 'selected' : '' }}>
              {{ $parent->name }}
            </option>
            @foreach($parent->children as $child)
              <option value="{{ $child->id }}" {{ old('category_id', $product->category_id ?? '') == $child->id ? 'selected' : '' }}>
                &nbsp;&nbsp;└ {{ $child->name }}
              </option>
            @endforeach
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Mã SKU</label>
        <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku ?? '') }}">
      </div>
      <div class="form-group">
        <label>Thương hiệu</label>
        <input type="text" name="brand" class="form-control" value="{{ old('brand', $product->brand ?? '') }}">
      </div>
    </div>

    <div class="form-row-3">
      <div class="form-group">
        <label>Giá gốc</label>
        <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $product->price ?? 0) }}">
      </div>
      <div class="form-group">
        <label>Giá khuyến mãi</label>
        <input type="number" step="0.01" name="sale_price" class="form-control" value="{{ old('sale_price', $product->sale_price ?? '') }}">
      </div>
      <div class="form-group">
        <label>Tồn kho</label>
        <input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock ?? 0) }}">
      </div>
    </div>

    <div class="form-group">
      <label>Mô tả ngắn</label>
      <textarea name="short_desc" class="form-control rich-editor" rows="2">{{ old('short_desc', $product->short_desc ?? '') }}</textarea>
    </div>

    <div class="form-group">
      <label>Mô tả chi tiết</label>
      <textarea name="description" class="form-control rich-editor" rows="8">{{ old('description', $product->description ?? '') }}</textarea>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Ảnh sản phẩm</label>
        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImg(this,'p-img')">
        @if(isset($product) && $product->image)
          <img id="p-img" class="img-preview" src="{{ $product->image_url }}" alt="">
        @else
          <img id="p-img" class="img-preview" style="display:none" alt="">
        @endif
      </div>
      <div>
        <div class="form-group">
          <label class="form-check">
            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured ?? 0) ? 'checked' : '' }}>
            Sản phẩm nổi bật
          </label>
        </div>
        <div class="form-group">
          <label class="form-check">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active ?? 1) ? 'checked' : '' }}>
            Kích hoạt
          </label>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Lưu sản phẩm</button>
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
// Auto-generate slug from name
document.getElementById('p-name')?.addEventListener('input', function() {
  const slug = document.getElementById('p-slug');
  if (!slug.dataset.manual) {
    slug.value = this.value.toLowerCase()
      .normalize('NFD').replace(/[̀-ͯ]/g,'')
      .replace(/đ/g,'d').replace(/[^a-z0-9\s-]/g,'')
      .trim().replace(/\s+/g,'-');
  }
});
document.getElementById('p-slug')?.addEventListener('input', function() {
  this.dataset.manual = '1';
});
</script>
@endpush
