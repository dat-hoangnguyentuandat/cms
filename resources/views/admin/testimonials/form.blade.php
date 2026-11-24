@extends('layouts.admin')
@section('title', isset($testimonial) ? 'Sửa đánh giá' : 'Thêm đánh giá')

@section('content')
@php
  $placeData = old('place_data', $testimonial->place_data ?? []);
  $summaryData = old('summary_data', $testimonial->summary_data ?? []);
  $reviewMedia = old('review_media', $testimonial->review_media ?? []);
  $aboutItems = $summaryData['about_items'] ?? [];
@endphp

<div class="card">
  <div class="card-header">
    <h3>{{ isset($testimonial) ? 'Sửa' : 'Thêm' }} đánh giá</h3>
    <a href="{{ route('admin.testimonials.index') }}" class="btn btn-outline btn-sm">← Quay lại</a>
  </div>

  <form method="post"
        action="{{ isset($testimonial) ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}"
        enctype="multipart/form-data">
    @csrf
    @if(isset($testimonial)) @method('PUT') @endif

    <h4 style="margin:0 0 16px">Thông tin đánh giá</h4>
    <div class="form-row">
      <div class="form-group">
        <label>Tên khách <span class="req">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $testimonial->name ?? '') }}" required>
      </div>
      <div class="form-group">
        <label>Mô tả</label>
        <input type="text" name="role" class="form-control" value="{{ old('role', $testimonial->role ?? '') }}">
      </div>
    </div>

    <div class="form-group">
      <label>Nội dung đánh giá <span class="req">*</span></label>
      <textarea name="content" class="form-control rich-editor" rows="4" required>{{ old('content', $testimonial->content ?? '') }}</textarea>
    </div>

    <div class="form-row-3">
      <div class="form-group">
        <label>Điểm đánh giá</label>
        <select name="rating" class="form-control">
          @for($i = 5; $i >= 1; $i--)
            <option value="{{ $i }}" {{ old('rating', $testimonial->rating ?? 5) == $i ? 'selected' : '' }}>
              {{ str_repeat('★', $i) }} ({{ $i }})
            </option>
          @endfor
        </select>
      </div>
      <div class="form-group">
        <label>Thứ tự</label>
        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $testimonial->sort_order ?? 0) }}">
      </div>
      <div class="form-group" style="display:flex;align-items:end">
        <label class="form-check">
          <input type="checkbox" name="is_active" value="1" {{ old('is_active', $testimonial->is_active ?? 1) ? 'checked' : '' }}>
          Hiển thị
        </label>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Nguồn</label>
        <input type="text" name="source" class="form-control" value="{{ old('source', $testimonial->source ?? '') }}">
      </div>
      <div class="form-group">
        <label>Review ID</label>
        <input type="text" name="source_id" class="form-control" value="{{ old('source_id', $testimonial->source_id ?? '') }}">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Ngày review</label>
        <input type="text" name="relative_date" class="form-control" value="{{ old('relative_date', $testimonial->relative_date ?? '') }}">
      </div>
      <div class="form-group">
        <label>URL nguồn</label>
        <input type="url" name="source_url" class="form-control" value="{{ old('source_url', $testimonial->source_url ?? '') }}">
      </div>
    </div>

    <div class="form-group">
      <label>Phản hồi chủ sở hữu</label>
      <textarea name="owner_response" class="form-control" rows="3">{{ old('owner_response', $testimonial->owner_response ?? '') }}</textarea>
    </div>

    <div class="form-group">
      <label>Ảnh đại diện</label>
      <input type="file" name="avatar" class="form-control" accept="image/*" onchange="previewImg(this,'t-avatar')">
      @if(isset($testimonial) && $testimonial->avatar)
        <img id="t-avatar" class="img-preview" src="{{ $testimonial->image_url }}" alt="">
      @else
        <img id="t-avatar" class="img-preview" style="display:none" alt="">
      @endif
    </div>

    <hr style="margin:24px 0">
    <h4 style="margin:0 0 16px">Ảnh review</h4>
    <div id="reviewMediaList" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px">
      @forelse($reviewMedia as $mediaUrl)
        <div class="review-media-item" style="border:1px solid var(--border);border-radius:10px;padding:8px;background:#fff">
          <img src="{{ $mediaUrl }}" alt="Review media" style="width:100%;height:120px;object-fit:cover;border-radius:8px;background:#f3f4f6;margin-bottom:8px" onerror="this.style.display='none'">
          <input type="url" name="review_media[]" class="form-control" value="{{ $mediaUrl }}" placeholder="URL ảnh review">
          <button type="button" class="btn btn-danger btn-sm" style="margin-top:8px" onclick="this.closest('.review-media-item').remove()">Xóa ảnh</button>
        </div>
      @empty
      @endforelse
    </div>
    <button type="button" class="btn btn-outline btn-sm" style="margin-top:12px" onclick="addReviewMedia()">+ Thêm ảnh review</button>

    <hr style="margin:24px 0">
    <h4 style="margin:0 0 16px">Thông tin địa điểm</h4>
    <div class="form-row">
      <div class="form-group"><label>Place ID</label><input type="text" name="place_data[id]" class="form-control" value="{{ $placeData['id'] ?? '' }}"></div>
      <div class="form-group"><label>Tên địa điểm</label><input type="text" name="place_data[name]" class="form-control" value="{{ $placeData['name'] ?? '' }}"></div>
    </div>
    <div class="form-row-3">
      <div class="form-group"><label>Danh mục</label><input type="text" name="place_data[category]" class="form-control" value="{{ $placeData['category'] ?? '' }}"></div>
      <div class="form-group"><label>Rating Google</label><input type="text" name="place_data[rating]" class="form-control" value="{{ $placeData['rating'] ?? '' }}"></div>
      <div class="form-group"><label>Số review Google</label><input type="number" name="place_data[google_review_count]" class="form-control" value="{{ $placeData['google_review_count'] ?? '' }}"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Số điện thoại</label><input type="text" name="place_data[phone]" class="form-control" value="{{ $placeData['phone'] ?? '' }}"></div>
      <div class="form-group"><label>Email</label><input type="email" name="place_data[email]" class="form-control" value="{{ $placeData['email'] ?? '' }}"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Website</label><input type="url" name="place_data[website]" class="form-control" value="{{ $placeData['website'] ?? '' }}"></div>
      <div class="form-group"><label>Google Map URL</label><input type="url" name="place_data[google_map_url]" class="form-control" value="{{ $placeData['google_map_url'] ?? '' }}"></div>
    </div>
    <div class="form-group"><label>Địa chỉ</label><input type="text" name="place_data[address]" class="form-control" value="{{ $placeData['address'] ?? '' }}"></div>
    <div class="form-row-3">
      <div class="form-group"><label>Thành phố</label><input type="text" name="place_data[city]" class="form-control" value="{{ $placeData['city'] ?? '' }}"></div>
      <div class="form-group"><label>Quận/Huyện</label><input type="text" name="place_data[district]" class="form-control" value="{{ $placeData['district'] ?? '' }}"></div>
      <div class="form-group"><label>Tỉnh/Thành</label><input type="text" name="place_data[province]" class="form-control" value="{{ $placeData['province'] ?? '' }}"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Thumbnail</label><input type="url" name="place_data[thumbnail]" class="form-control" value="{{ $placeData['thumbnail'] ?? '' }}"></div>
      <div class="form-group"><label>Thời gian crawl</label><input type="text" name="place_data[crawled_at]" class="form-control" value="{{ $placeData['crawled_at'] ?? '' }}"></div>
    </div>
    <div class="form-row-3">
      <div class="form-group"><label>Facebook</label><input type="url" name="place_data[facebook]" class="form-control" value="{{ $placeData['facebook'] ?? '' }}"></div>
      <div class="form-group"><label>Instagram</label><input type="url" name="place_data[instagram]" class="form-control" value="{{ $placeData['instagram'] ?? '' }}"></div>
      <div class="form-group"><label>TikTok</label><input type="url" name="place_data[tiktok]" class="form-control" value="{{ $placeData['tiktok'] ?? '' }}"></div>
    </div>

    <hr style="margin:24px 0">
    <h4 style="margin:0 0 16px">Summary / Giới thiệu</h4>
    <div class="form-row">
      <div class="form-group"><label>Tên summary</label><input type="text" name="summary_data[name]" class="form-control" value="{{ $summaryData['name'] ?? '' }}"></div>
      <div class="form-group"><label>Danh mục summary</label><input type="text" name="summary_data[category]" class="form-control" value="{{ $summaryData['category'] ?? '' }}"></div>
    </div>
    <div class="form-row-3">
      <div class="form-group"><label>Rating</label><input type="text" name="summary_data[rating]" class="form-control" value="{{ $summaryData['rating'] ?? '' }}"></div>
      <div class="form-group"><label>Rating text</label><input type="text" name="summary_data[rating_text]" class="form-control" value="{{ $summaryData['rating_text'] ?? '' }}"></div>
      <div class="form-group"><label>Thumbnail</label><input type="url" name="summary_data[thumbnail]" class="form-control" value="{{ $summaryData['thumbnail'] ?? '' }}"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Google Map URL</label><input type="url" name="summary_data[google_map_url]" class="form-control" value="{{ $summaryData['google_map_url'] ?? '' }}"></div>
      <div class="form-group"><label>Website</label><input type="url" name="summary_data[website]" class="form-control" value="{{ $summaryData['website'] ?? '' }}"></div>
    </div>
    <div class="form-group"><label>Description</label><textarea name="summary_data[description]" class="form-control" rows="3">{{ $summaryData['description'] ?? '' }}</textarea></div>
    <div class="form-group"><label>About</label><textarea name="summary_data[about]" class="form-control" rows="3">{{ $summaryData['about'] ?? '' }}</textarea></div>

    <hr style="margin:24px 0">
    <details class="about-items-section" style="margin-top:8px">
      <summary style="cursor:pointer;display:flex;align-items:center;gap:10px;list-style:none;user-select:none">
        <span class="about-toggle-arrow" aria-hidden="true" style="display:inline-flex;width:18px;height:18px;align-items:center;justify-content:center;transition:transform .2s">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg>
        </span>
        <h4 style="margin:0">About items <span style="font-weight:400;color:var(--text-2);font-size:14px">({{ count($aboutItems) }})</span></h4>
      </summary>

      <div id="aboutItemsList" style="margin-top:16px">
        @foreach($aboutItems as $index => $item)
          <div class="about-item form-row" style="align-items:end">
            <div class="form-group"><label>Label</label><input type="text" name="summary_data[about_items][{{ $index }}][label]" class="form-control" value="{{ $item['label'] ?? '' }}"></div>
            <div class="form-group"><label>Text</label><input type="text" name="summary_data[about_items][{{ $index }}][text]" class="form-control" value="{{ $item['text'] ?? '' }}"></div>
            <button type="button" class="btn btn-danger btn-sm" style="margin-bottom:16px" onclick="this.closest('.about-item').remove()">Xóa</button>
          </div>
        @endforeach
      </div>
      <button type="button" class="btn btn-outline btn-sm" onclick="addAboutItem()">+ Thêm about item</button>
    </details>

    @if(isset($testimonial) && $testimonial->metadata)
      <details style="margin-top:24px">
        <summary style="cursor:pointer;font-weight:600">Dữ liệu kỹ thuật / Raw JSON</summary>
        <textarea class="form-control" rows="10" readonly style="margin-top:12px">{{ json_encode($testimonial->metadata, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</textarea>
      </details>
    @endif

    <button type="submit" class="btn btn-primary" style="margin-top:24px">Lưu</button>
  </form>
</div>
@endsection

@push('css')
<style>
.about-items-section > summary::-webkit-details-marker { display: none; }
.about-items-section > summary::marker { content: ''; }
.about-items-section[open] .about-toggle-arrow { transform: rotate(90deg); }
</style>
@endpush

@push('js')
<script>
let aboutIndex = {{ count($aboutItems) }};

function previewImg(input, id) {
  const img = document.getElementById(id);
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => { img.src = e.target.result; img.style.display = ''; };
    reader.readAsDataURL(input.files[0]);
  }
}

function addReviewMedia() {
  const list = document.getElementById('reviewMediaList');
  const item = document.createElement('div');
  item.className = 'review-media-item';
  item.style = 'border:1px solid var(--border);border-radius:10px;padding:8px;background:#fff';
  item.innerHTML = `
    <input type="url" name="review_media[]" class="form-control" placeholder="URL ảnh review">
    <button type="button" class="btn btn-danger btn-sm" style="margin-top:8px" onclick="this.closest('.review-media-item').remove()">Xóa ảnh</button>
  `;
  list.appendChild(item);
}

function addAboutItem() {
  const list = document.getElementById('aboutItemsList');
  const item = document.createElement('div');
  item.className = 'about-item form-row';
  item.style = 'align-items:end';
  item.innerHTML = `
    <div class="form-group"><label>Label</label><input type="text" name="summary_data[about_items][${aboutIndex}][label]" class="form-control"></div>
    <div class="form-group"><label>Text</label><input type="text" name="summary_data[about_items][${aboutIndex}][text]" class="form-control"></div>
    <button type="button" class="btn btn-danger btn-sm" style="margin-bottom:16px" onclick="this.closest('.about-item').remove()">Xóa</button>
  `;
  aboutIndex++;
  list.appendChild(item);
}
</script>
@endpush
