@extends('layouts.admin')
@section('title','Đánh giá khách hàng')

@section('content')
<div class="card">
  <div class="card-header">
    <h3>Đánh giá khách hàng ({{ number_format($testimonials->total()) }})</h3>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
      <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('importApiModal').style.display='flex'">
        Import từ Google API
      </button>
      <form method="post" action="{{ route('admin.testimonials.import-google-reviews') }}" enctype="multipart/form-data" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap" onsubmit="showImportLoading('Đang import file JSON...')">
        @csrf
        <input type="file" name="reviews_json" accept=".json,application/json" style="display:none" id="reviews-json-input" onchange="this.form.submit()">
        <label for="reviews-json-input" class="btn btn-outline btn-sm" style="cursor:pointer">Import JSON file</label>
      </form>
      <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Thêm đánh giá
      </a>
    </div>
  </div>

  <div id="importApiModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:1000;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:12px;padding:24px;max-width:560px;width:100%;box-shadow:0 20px 60px rgba(15,23,42,.25)">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <h4 style="margin:0">Import đánh giá từ Google API</h4>
        <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('importApiModal').style.display='none'">Đóng</button>
      </div>
      <form method="post" action="{{ route('admin.testimonials.import-google-api') }}" id="importApiForm" onsubmit="onImportApiSubmit(event)">
        @csrf
        <div class="form-group">
          <label>API detail URL</label>
          <input type="url" name="api_detail_url" class="form-control" placeholder="https://api-map.19t.vn/api/v1/google/places/.../detail?reviewLimit=20&reviewOffset=0">
          <small style="color:var(--text-2);font-size:12px;display:block;margin-top:4px">Dán URL API detail nếu đã có place UUID. Nếu nhập ô này, hệ thống sẽ import trực tiếp từ API URL.</small>
        </div>
        <div class="form-group">
          <label>Google Map URL</label>
          <input type="url" name="google_map_url" class="form-control" placeholder="https://www.google.com/maps/place/...">
          <small style="color:var(--text-2);font-size:12px;display:block;margin-top:4px">Dùng khi muốn crawl từ link Google Map thay vì API detail URL.</small>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Số review tối đa</label>
            <input type="number" name="max" class="form-control" value="20" min="1" max="100" required>
            <small style="color:var(--text-2);font-size:12px;display:block;margin-top:4px">Tối đa 100 review mỗi lần import.</small>
          </div>
          <div class="form-group">
            <label>API Key (tuỳ chọn)</label>
            <input type="text" name="api_key" class="form-control" placeholder="Để trống nếu đã cấu hình sẵn">
          </div>
        </div>
        <div class="form-group">
          <label class="form-check" style="display:flex;align-items:center;gap:12px;cursor:pointer">
            <input type="hidden" name="overwrite" value="0">
            <input type="checkbox" name="overwrite" value="1" id="importOverwriteToggle"
              style="width:20px;height:20px;cursor:pointer;accent-color:var(--danger,#dc2626)">
            <span style="font-weight:600;font-size:14px">Ghi đè dữ liệu đánh giá hiện tại</span>
          </label>
          <small style="color:var(--text-2);font-size:12px;display:block;margin-top:4px">Khi bật, toàn bộ đánh giá nguồn Google đang có sẽ bị xóa trước khi import dữ liệu mới. Tắt thì chỉ thêm mới và cập nhật, giữ lại đánh giá cũ.</small>
        </div>
        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px">
          <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('importApiModal').style.display='none'">Huỷ</button>
          <button type="submit" class="btn btn-primary" id="importApiSubmitBtn">Import</button>
        </div>
      </form>
    </div>
  </div>

  <div id="importLoadingOverlay" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:1100;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:12px;padding:28px 32px;max-width:440px;width:100%;text-align:center;box-shadow:0 20px 60px rgba(15,23,42,.25)">
      <div style="display:flex;justify-content:center;margin-bottom:16px">
        <div class="import-spinner" aria-hidden="true"></div>
      </div>
      <h4 style="margin:0 0 8px" id="importLoadingTitle">Đang xử lý...</h4>
      <p id="importLoadingMessage" style="margin:0;color:var(--text-2);font-size:14px;line-height:1.5">
        Quá trình này có thể mất vài phút xin vui lòng Chờ.
      </p>
      <p id="importLoadingTimer" style="margin:12px 0 0;font-size:13px;color:var(--text-2)">
        Đã chờ: <span id="importLoadingSeconds">0</span>s
      </p>
    </div>
  </div>

  @include('admin.partials.list-filters', [
    'clearUrl' => route('admin.testimonials.index'),
    'hasActiveFilters' => request()->filled('q') || request()->filled('status') || request()->filled('rating') || request()->filled('sort_from') || request()->filled('sort_to') || request()->filled('from') || request()->filled('to'),
    'fields' => [
      ['type' => 'text', 'name' => 'q', 'value' => $q, 'placeholder' => 'Tìm khách, vai trò, nội dung...'],
      ['type' => 'select', 'name' => 'rating', 'value' => $rating, 'label' => 'Số sao', 'options' => ['5' => '5 sao', '4' => '4 sao', '3' => '3 sao', '2' => '2 sao', '1' => '1 sao']],
      ['type' => 'select', 'name' => 'status', 'value' => $status, 'label' => 'Hiển thị', 'options' => ['1' => 'Hiện', '0' => 'Ẩn']],
      ['type' => 'number', 'name' => 'sort_from', 'value' => $sortFrom, 'placeholder' => 'Thứ tự từ'],
      ['type' => 'number', 'name' => 'sort_to', 'value' => $sortTo, 'placeholder' => 'Thứ tự đến'],
      ['type' => 'date', 'name' => 'from', 'value' => $from, 'placeholder' => 'Từ ngày'],
      ['type' => 'date', 'name' => 'to', 'value' => $to, 'placeholder' => 'Đến ngày'],
    ],
  ])

  @if($testimonials->isEmpty())
    <div class="empty-state">Chưa có đánh giá nào.</div>
  @else
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>#</th><th>Khách</th><th>Nội dung</th><th>Sao</th><th>Thứ tự</th><th>Hiển thị</th><th></th></tr></thead>
      <tbody>
      @foreach($testimonials as $t)
        <tr>
          <td>{{ $t->id }}</td>
          <td>
            <b>{{ $t->name }}</b>
            <div style="font-size:12px;color:var(--text-2)">{{ $t->role }}</div>
          </td>
          <td style="max-width:400px">{{ mb_substr($t->content, 0, 120) }}{{ mb_strlen($t->content) > 120 ? '...' : '' }}</td>
          <td style="color:#f59e0b">{{ str_repeat('★', $t->rating) }}</td>
          <td>{{ $t->sort_order }}</td>
          <td>
            @if($t->is_active)
              <span class="badge badge-success">Hiện</span>
            @else
              <span class="badge badge-secondary">Ẩn</span>
            @endif
          </td>
          <td>
            <div class="actions">
              <a href="{{ route('admin.testimonials.edit', $t) }}" class="btn btn-outline btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </a>
              <form method="post" action="{{ route('admin.testimonials.destroy', $t) }}" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xóa đánh giá này?')">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
              </form>
            </div>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  @if($testimonials->hasPages())
  <div class="pagination">
    {{ $testimonials->links('vendor.pagination.admin') }}
  </div>
  @endif
  @endif
</div>

@push('css')
<style>
.import-spinner {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  border: 4px solid rgba(99, 102, 241, .15);
  border-top-color: var(--primary, #6366f1);
  animation: import-spin .9s linear infinite;
}
@keyframes import-spin {
  to { transform: rotate(360deg); }
}
</style>
@endpush

@push('js')
<script>
let importLoadingTimerId = null;
let importLoadingStart = 0;

function showImportLoading(title) {
  const overlay = document.getElementById('importLoadingOverlay');
  if (!overlay) return;
  if (title) {
    document.getElementById('importLoadingTitle').textContent = title;
  }
  overlay.style.display = 'flex';
  document.body.style.overflow = 'hidden';
  importLoadingStart = Date.now();
  document.getElementById('importLoadingSeconds').textContent = '0';
  if (importLoadingTimerId) clearInterval(importLoadingTimerId);
  importLoadingTimerId = setInterval(() => {
    const sec = Math.floor((Date.now() - importLoadingStart) / 1000);
    document.getElementById('importLoadingSeconds').textContent = sec;
  }, 1000);
}

function onImportApiSubmit(e) {
  const form = document.getElementById('importApiForm');
  const apiUrl = form?.querySelector('[name="api_detail_url"]')?.value.trim();
  const mapUrl = form?.querySelector('[name="google_map_url"]')?.value.trim();
  if (!apiUrl && !mapUrl) {
    e.preventDefault();
    alert('Vui lòng nhập API detail URL hoặc Google Map URL.');
    return false;
  }
  const apiModal = document.getElementById('importApiModal');
  if (apiModal) apiModal.style.display = 'none';
  const btn = document.getElementById('importApiSubmitBtn');
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Đang crawl...';
  }
  showImportLoading('Đang crawl Google reviews từ API...');
}
</script>
@endpush
@endsection
