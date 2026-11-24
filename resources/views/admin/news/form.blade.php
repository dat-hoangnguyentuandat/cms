@extends('layouts.admin')
@section('title', isset($post) ? 'Sửa bài viết' : 'Thêm bài viết')

@section('content')
<div class="card">
  <div class="card-header">
    <h3>{{ isset($post) ? 'Sửa' : 'Thêm' }} bài viết</h3>
    <a href="{{ route('admin.news.index') }}" class="btn btn-outline btn-sm">← Quay lại</a>
  </div>

  <form method="post"
        action="{{ isset($post) ? route('admin.news.update', $post) : route('admin.news.store') }}"
        enctype="multipart/form-data">
    @csrf
    @if(isset($post)) @method('PUT') @endif

    <div class="form-row">
      <div class="form-group">
        <label>Tiêu đề <span class="req">*</span></label>
        <input type="text" name="title" id="news-title" class="form-control" value="{{ old('title', $post->title ?? '') }}" required>
      </div>
      <div class="form-group">
        <label>Slug</label>
        <input type="text" name="slug" id="news-slug" class="form-control" value="{{ old('slug', $post->slug ?? '') }}">
      </div>
    </div>

    <div class="form-group">
      <label>Danh mục</label>
      <select name="category_id" class="form-control">
        <option value="">— Không có danh mục —</option>
        @foreach($categories as $c)
          <option value="{{ $c->id }}" {{ old('category_id', $post->category_id ?? '') == $c->id ? 'selected' : '' }}>
            {{ $c->name }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="form-group">
      <label>Mô tả ngắn</label>
      <textarea name="excerpt" class="form-control rich-editor" rows="2">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
    </div>

    <div class="form-group">
      <label>Nội dung</label>
      <textarea name="content" class="form-control rich-editor" rows="12">{{ old('content', $post->content ?? '') }}</textarea>
      <div class="form-help">Có thể nhập HTML.</div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Ảnh bài viết</label>
        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImg(this,'news-img')">
        @if(isset($post) && $post->image)
          <img id="news-img" class="img-preview" src="{{ $post->image_url }}" alt="">
        @else
          <img id="news-img" class="img-preview" style="display:none" alt="">
        @endif
      </div>
      <div>
        <div class="form-group">
          <label>Tác giả</label>
          <input type="text" name="author" class="form-control" value="{{ old('author', $post->author ?? session('admin_user.full_name', '')) }}">
        </div>
        <div class="form-group">
          <label class="form-check">
            <input type="checkbox" name="is_published" value="1" {{ old('is_published', $post->is_published ?? 1) ? 'checked' : '' }}>
            Công khai
          </label>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Lưu bài viết</button>
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
document.getElementById('news-title')?.addEventListener('input', function() {
  const slug = document.getElementById('news-slug');
  if (!slug.dataset.manual) {
    slug.value = this.value.toLowerCase()
      .normalize('NFD').replace(/[̀-ͯ]/g,'')
      .replace(/đ/g,'d').replace(/[^a-z0-9\s-]/g,'')
      .trim().replace(/\s+/g,'-');
  }
});
document.getElementById('news-slug')?.addEventListener('input', function() {
  this.dataset.manual = '1';
});
</script>
@endpush
