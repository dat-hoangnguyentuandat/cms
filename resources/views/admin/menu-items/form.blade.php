@extends('layouts.admin')
@section('title', isset($menuItem) ? 'Sửa menu item' : 'Thêm menu item')

@php
  $type = old('type', $menuItem->type ?? $defaultType ?? 'custom');
@endphp

@section('content')
<div class="card">
  <div class="card-header">
    <h3>{{ isset($menuItem) ? 'Sửa' : 'Thêm' }} menu item</h3>
    <a href="{{ route('admin.menu-items.index') }}" class="btn btn-outline btn-sm">← Quay lại</a>
  </div>

  <form method="post" enctype="multipart/form-data"
        action="{{ isset($menuItem) ? route('admin.menu-items.update', $menuItem) : route('admin.menu-items.store') }}">
    @csrf
    @if(isset($menuItem)) @method('PUT') @endif

    <div style="display:grid;grid-template-columns:1fr 260px;gap:24px;align-items:start">

      {{-- Main column --}}
      <div>

        {{-- Từ khóa trang --}}
        <div class="form-group">
          <label>Từ khóa trang <span class="req">*</span></label>
          <input type="text" name="keyword" class="form-control"
                 value="{{ old('keyword', $menuItem->keyword ?? '') }}"
                 placeholder="từ khóa có dấu, tu khoa khong dau">
          <div class="form-help">Chèn 1 từ khóa với 2 phiên bản có dấu và không dấu, cách nhau bằng dấu phẩy.</div>
          @error('keyword')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        {{-- Tên menu --}}
        <div class="form-group">
          <label>Tên menu <span class="req">*</span></label>
          <input type="text" name="label" class="form-control"
                 value="{{ old('label', $menuItem->label ?? '') }}" required>
          @error('label')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <input type="hidden" name="type" value="{{ $type }}">

        {{-- Type badge (read-only) --}}
        <div style="margin-bottom:16px">
          <span style="font-size:12px;color:var(--text-2)">Loại trang: </span>
          <span class="badge {{ \App\Models\MenuItem::typeBadgeClass($type) }}">
            {{ \App\Models\MenuItem::typeLabel($type) }}
          </span>
        </div>

        {{-- Tên thể loại (chỉ hiện với trang sản phẩm / dịch vụ) --}}
        @if(in_array($type, ['product_list', 'service_list']))
        <div class="form-group">
          <label>Tên thể loại <span class="req">*</span></label>
          <input type="text" name="section_name" class="form-control"
                 value="{{ old('section_name', $menuItem->section_name ?? '') }}"
                 placeholder="{{ $type === 'product_list' ? 'VD: Sản phẩm nổi bật' : 'VD: Dịch vụ của chúng tôi' }}">
          <div class="form-help">Tên hiển thị cho khu vực {{ $type === 'product_list' ? 'sản phẩm' : 'dịch vụ' }} trên trang.</div>
          @error('section_name')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        @endif

        {{-- Tiêu đề H1 --}}
        <div class="form-group">
          <label>Tiêu đề (H1)</label>
          <input type="text" name="title" class="form-control"
                 value="{{ old('title', $menuItem->title ?? '') }}"
                 placeholder="Tiêu đề trang, chứa 1 lần từ khóa để chuẩn SEO">
          <div class="form-help">Thẻ H1 — nên chèn 1 lần từ khóa để chuẩn SEO.</div>
          @error('title')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        {{-- Mô tả --}}
        <div class="form-group">
          <label>Mô tả</label>
          <textarea name="description" class="form-control rich-editor" rows="4"
                    placeholder="Viết thành 1 đoạn văn ngắn, chứa 1-2 lần từ khóa">{{ old('description', $menuItem->description ?? '') }}</textarea>
          <div class="form-help">Viết thành 1 đoạn văn ngắn, chứa 1–2 lần từ khóa.</div>
          @error('description')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        {{-- SEO --}}
        <div style="margin:20px 0 4px;display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
          <div>
            <div style="font-weight:600;font-size:14px;color:var(--text-1);margin-bottom:2px">Tối ưu SEO</div>
            <div style="font-size:12px;color:var(--text-2)">Thiết lập các thẻ mô tả giúp khách hàng dễ dàng tìm thấy trên Google.</div>
          </div>
          <button type="button" onclick="generateSeo()" style="flex-shrink:0;padding:6px 14px;background:#26a69a;color:#fff;border:none;border-radius:4px;font-size:13px;cursor:pointer">
            Tạo SEO
          </button>
        </div>

        <div class="form-group">
          <label>
            Tiêu đề Trang (&lt;title&gt;)
            <span id="seo-title-count" style="float:right;font-size:12px;color:var(--text-2)">0/70 ký tự</span>
          </label>
          <input type="text" name="seo_title" id="seo-title" class="form-control" maxlength="70"
                 value="{{ old('seo_title', $menuItem->seo_title ?? '') }}"
                 placeholder="Viết thành 1 câu thu hút, chứa 1 lần từ khóa">
          <div class="form-help">Viết thành 1 câu thu hút, chứa 1 lần từ khóa.</div>
          @error('seo_title')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
          <label>
            Mô Tả Trang (&lt;meta description&gt;)
            <span id="seo-desc-count" style="float:right;font-size:12px;color:var(--text-2)">0/160 ký tự</span>
          </label>
          <textarea name="seo_description" id="seo-desc" class="form-control" rows="3" maxlength="160"
                    placeholder="Viết thành đoạn văn ngắn chứa 1-2 lần từ khóa">{{ old('seo_description', $menuItem->seo_description ?? '') }}</textarea>
          <div class="form-help">Viết thành đoạn văn ngắn chứa 1–2 lần từ khóa.</div>
          @error('seo_description')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
          <label>Đường dẫn <span class="req">*</span></label>
          <input type="text" name="slug" id="seo-slug" class="form-control"
                 value="{{ old('slug', $menuItem->slug ?? '') }}"
                 placeholder="duong-dan-trang">
          <div class="form-help">Chỉ dùng chữ thường, số và dấu gạch ngang. VD: gioi-thieu</div>
          @error('slug')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        {{-- Google preview --}}
        <div style="margin-top:16px;padding:14px 16px;background:#f8f9fa;border-radius:8px;border:1px solid var(--border)">
          <div style="font-size:11px;color:var(--text-2);margin-bottom:8px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">KHI LÊN TOP PAGE SẼ HIỂN THỊ NHƯ SAU:</div>
          <div id="gp-title" style="font-size:18px;color:#1a0dab;font-weight:400;line-height:1.3;margin-bottom:2px">{{ old('seo_title', $menuItem->seo_title ?? '') }}</div>
          <div id="gp-url" style="font-size:13px;color:#006621;margin-bottom:4px">{{ request()->getSchemeAndHttpHost() }}/<span id="gp-slug">{{ old('slug', $menuItem->slug ?? '') }}</span></div>
          <div id="gp-desc" style="font-size:13px;color:#545454;line-height:1.5">{{ old('seo_description', $menuItem->seo_description ?? '') }}</div>
        </div>

        {{-- SEO bottom bar --}}
        @php $seoOn = isset($menuItem) && ($menuItem->seo_title || $menuItem->seo_description); @endphp
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:12px;gap:8px">
          <div style="display:flex;align-items:center;gap:10px">
            <button type="button" id="seo-toggle-btn" onclick="toggleSeo(this)"
                    style="padding:7px 18px;border:none;border-radius:4px;font-size:13px;cursor:pointer;background:{{ $seoOn ? '#26a69a' : '#9e9e9e' }};color:#fff;transition:background .2s">
              <span id="seo-toggle-label">{{ $seoOn ? 'Đang bật' : 'Đang tắt' }}</span>
            </button>
            <span style="font-size:12px;color:var(--text-2)">Index, Follow</span>
          </div>
          <button type="button" onclick="checkSeo()"
                  style="padding:7px 18px;border:none;border-radius:4px;font-size:13px;cursor:pointer;background:#4caf50;color:#fff">
            Kiểm tra SEO
          </button>
        </div>

        <input type="hidden" name="target" value="{{ old('target', $menuItem->target ?? '_self') }}">

        <div class="form-row">
          <div class="form-group">
            <label>Thứ tự</label>
            <input type="number" name="sort_order" class="form-control"
                   value="{{ old('sort_order', $menuItem->sort_order ?? 0) }}">
            @error('sort_order')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:8px">
            <label class="form-check">
              <input type="checkbox" name="is_active" value="1"
                     {{ old('is_active', $menuItem->is_active ?? 1) ? 'checked' : '' }}>
              Kích hoạt
            </label>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">Lưu menu item</button>

      </div>{{-- end main column --}}

      {{-- Sidebar --}}
      <div>

        {{-- Phân loại --}}
        <div style="border:1px solid var(--border);border-radius:8px;overflow:hidden;margin-bottom:16px">
          <div style="padding:12px 16px;font-weight:600;font-size:13px;border-bottom:1px solid var(--border)">
            Phân loại
          </div>
          <div style="padding:16px">
            <select name="parent_id" class="form-control">
              <option value="">-- Không có (menu gốc) --</option>
              @foreach($parents as $p)
                <option value="{{ $p->id }}" {{ old('parent_id', $menuItem->parent_id ?? $defaultParentId ?? '') == $p->id ? 'selected' : '' }}>
                  {{ $p->label }}
                </option>
              @endforeach
            </select>
            @error('parent_id')<div class="form-error" style="margin-top:4px">{{ $message }}</div>@enderror
            <div class="form-help" style="margin-top:6px">Chỉ menu gốc có thể làm cha.</div>
          </div>
        </div>

        {{-- Hình chia sẻ --}}
        <div style="border:1px solid var(--border);border-radius:8px;overflow:hidden">
          <div style="padding:12px 16px;font-weight:600;font-size:13px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
            Hình chia sẻ
            <span style="font-size:11px;font-weight:400;color:var(--text-2)">(Kích thước tối thiểu 200x200)</span>
          </div>
          <div style="padding:16px">
            @php $imgUrl = isset($menuItem) ? $menuItem->image_url : ''; @endphp
            @if($imgUrl)
              <img id="menu-img-preview" src="{{ $imgUrl }}" alt=""
                   style="width:100%;border-radius:6px;margin-bottom:10px;display:block">
            @else
              <div id="menu-img-placeholder"
                   style="width:100%;aspect-ratio:1;background:#f3f4f6;border-radius:6px;display:flex;align-items:center;justify-content:center;margin-bottom:10px;color:var(--text-2);font-size:13px">
                No Image
              </div>
              <img id="menu-img-preview" src="" alt=""
                   style="width:100%;border-radius:6px;margin-bottom:10px;display:none">
            @endif
            <label class="btn btn-outline btn-sm" style="width:100%;text-align:center;cursor:pointer">
              Chọn hình
              <input type="file" name="image" accept="image/*" style="display:none"
                     onchange="previewMenuImg(this)">
            </label>
            @error('image')<div class="form-error" style="margin-top:6px">{{ $message }}</div>@enderror
          </div>
        </div>
      </div>{{-- end sidebar --}}

    </div>{{-- end grid --}}
  </form>
</div>
@endsection

@push('js')
<script>
function previewMenuImg(input) {
  if (!input.files || !input.files[0]) return;
  var reader = new FileReader();
  reader.onload = function(e) {
    var preview = document.getElementById('menu-img-preview');
    var placeholder = document.getElementById('menu-img-placeholder');
    preview.src = e.target.result;
    preview.style.display = 'block';
    if (placeholder) placeholder.style.display = 'none';
  };
  reader.readAsDataURL(input.files[0]);
}

function updateCount(inputId, countId, max) {
  var el = document.getElementById(inputId);
  var counter = document.getElementById(countId);
  if (!el || !counter) return;
  counter.textContent = el.value.length + '/' + max + ' ký tự';
  el.addEventListener('input', function() {
    counter.textContent = this.value.length + '/' + max + ' ký tự';
  });
}
updateCount('seo-title', 'seo-title-count', 70);
updateCount('seo-desc',  'seo-desc-count',  160);

// Google preview live update
var gpTitle = document.getElementById('gp-title');
var gpDesc  = document.getElementById('gp-desc');
var gpSlug  = document.getElementById('gp-slug');

document.getElementById('seo-title').addEventListener('input', function() {
  gpTitle.textContent = this.value;
});
document.getElementById('seo-desc').addEventListener('input', function() {
  gpDesc.textContent = this.value;
});
document.getElementById('seo-slug').addEventListener('input', function() {
  gpSlug.textContent = this.value;
});

// SEO toggle
var seoOn = {{ $seoOn ? 'true' : 'false' }};
function toggleSeo(btn) {
  seoOn = !seoOn;
  btn.style.background = seoOn ? '#26a69a' : '#9e9e9e';
  document.getElementById('seo-toggle-label').textContent = seoOn ? 'Đang bật' : 'Đang tắt';
}

function generateSeo() {
  var keyword = document.querySelector('[name="keyword"]').value.trim().split(',')[0].trim();
  var label   = document.querySelector('[name="label"]').value.trim();
  var titleEl = document.getElementById('seo-title');
  var descEl  = document.getElementById('seo-desc');
  var slugEl  = document.getElementById('seo-slug');

  if (!titleEl.value && label) {
    titleEl.value = label + (keyword ? ' - ' + keyword : '');
    gpTitle.textContent = titleEl.value;
    updateCount('seo-title', 'seo-title-count', 70);
  }
  if (!descEl.value && keyword) {
    descEl.value = 'Tìm hiểu về ' + keyword + '. ' + label + ' cung cấp thông tin chi tiết và đầy đủ nhất.';
    gpDesc.textContent = descEl.value;
    updateCount('seo-desc', 'seo-desc-count', 160);
  }
  if (!slugEl.value && label) {
    var slug = label.toLowerCase()
      .normalize('NFD').replace(/[̀-ͯ]/g, '')
      .replace(/đ/g,'d').replace(/[^a-z0-9\s-]/g,'')
      .trim().replace(/\s+/g,'-');
    slugEl.value = slug;
    gpSlug.textContent = slug;
  }
}

function checkSeo() {
  var issues = [];
  var title = document.getElementById('seo-title').value;
  var desc  = document.getElementById('seo-desc').value;
  var slug  = document.getElementById('seo-slug').value;
  var keyword = document.querySelector('[name="keyword"]').value.trim().split(',')[0].trim().toLowerCase();

  if (!title) issues.push('Chưa có tiêu đề trang (seo title).');
  else if (title.length < 30) issues.push('Tiêu đề trang quá ngắn (nên 30–70 ký tự).');
  else if (keyword && title.toLowerCase().indexOf(keyword) === -1) issues.push('Tiêu đề trang chưa chứa từ khóa.');

  if (!desc) issues.push('Chưa có mô tả trang (meta description).');
  else if (desc.length < 70) issues.push('Mô tả trang quá ngắn (nên 70–160 ký tự).');
  else if (keyword && desc.toLowerCase().indexOf(keyword) === -1) issues.push('Mô tả trang chưa chứa từ khóa.');

  if (!slug) issues.push('Chưa có đường dẫn (slug).');

  if (issues.length === 0) alert('SEO ổn! Không phát hiện vấn đề.');
  else alert('Cần cải thiện:\n• ' + issues.join('\n• '));
}
</script>
@endpush
