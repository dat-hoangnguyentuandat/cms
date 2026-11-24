@extends('layouts.admin')

@section('title')
LocalBusiness - Structured Data
@endsection

@section('content')
<div class="card">
  <div class="card-header">
    <h3>LocalBusiness - Structured Data ({{ number_format($businesses->total()) }})</h3>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
      <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('importCrawlModal').style.display='flex'">
        Import từ Google Places
      </button>
      <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('importJsonLdModal').style.display='flex'">
        Import từ JSON-LD
      </button>
      <a href="{{ route('admin.local-businesses.create') }}" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Thêm Business
      </a>
    </div>
  </div>

  <!-- Import từ Google Places Modal -->
  <div id="importCrawlModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:1000;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:12px;padding:24px;max-width:560px;width:100%;box-shadow:0 20px 60px rgba(15,23,42,.25)">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <h4 style="margin:0">Import từ Google Places</h4>
        <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('importCrawlModal').style.display='none'">Đóng</button>
      </div>
      <form method="post" action="{{ route('admin.local-businesses.import-from-crawl') }}" onsubmit="showImportLoading()">
        @csrf
        <div class="form-group">
          <label>Google Map URL <span class="req">*</span></label>
          <input type="url" name="google_map_url" class="form-control" placeholder="https://www.google.com/maps/place/..." required>
          <small style="color:var(--text-2);font-size:12px;display:block;margin-top:4px">URL từ Google Maps của doanh nghiệp.</small>
        </div>
        <div class="form-group">
          <label>API Key (tuỳ chọn)</label>
          <input type="text" name="api_key" class="form-control" placeholder="Để trống nếu đã cấu hình sẵn">
        </div>
        <div class="form-group">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
            <input type="checkbox" name="set_as_primary" value="1" checked>
            <span>Đặt làm business chính (primary)</span>
          </label>
          <small style="color:var(--text-2);font-size:12px;display:block;margin-top:4px">Business chính sẽ được hiển thị structured data trên website.</small>
        </div>
        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px">
          <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('importCrawlModal').style.display='none'">Huỷ</button>
          <button type="submit" class="btn btn-primary">Import</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Import từ JSON-LD Modal -->
  <div id="importJsonLdModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:1000;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:12px;padding:24px;max-width:680px;width:100%;box-shadow:0 20px 60px rgba(15,23,42,.25)">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <h4 style="margin:0">Import từ JSON-LD</h4>
        <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('importJsonLdModal').style.display='none'">Đóng</button>
      </div>
      <form method="post" action="{{ route('admin.local-businesses.import-from-json-ld') }}">
        @csrf
        <div class="form-group">
          <label>JSON-LD Structured Data <span class="req">*</span></label>
          <textarea name="json_ld" class="form-control" rows="12" placeholder='{"@@context": "https://schema.org", "@@type": "LocalBusiness", "name": "...", ...}' required style="font-family:monospace;font-size:13px"></textarea>
          <small style="color:var(--text-2);font-size:12px;display:block;margin-top:4px">Paste JSON-LD structured data theo chuẩn schema.org.</small>
        </div>
        <div class="form-group">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
            <input type="checkbox" name="set_as_primary" value="1" checked>
            <span>Đặt làm business chính (primary)</span>
          </label>
          <small style="color:var(--text-2);font-size:12px;display:block;margin-top:4px">Business chính sẽ được hiển thị structured data trên website.</small>
        </div>
        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px">
          <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('importJsonLdModal').style.display='none'">Huỷ</button>
          <button type="submit" class="btn btn-primary">Import</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Loading Overlay -->
  <div id="importLoadingOverlay" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:1100;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:12px;padding:28px 32px;max-width:440px;width:100%;text-align:center;box-shadow:0 20px 60px rgba(15,23,42,.25)">
      <div style="display:flex;justify-content:center;margin-bottom:16px">
        <div class="import-spinner" aria-hidden="true"></div>
      </div>
      <h4 style="margin:0 0 8px">Đang import từ Google Places...</h4>
      <p style="margin:0;color:var(--text-2);font-size:14px;line-height:1.5">
        Quá trình này có thể mất vài phút, xin vui lòng chờ.
      </p>
    </div>
  </div>

  @if($businesses->isEmpty())
    <div class="empty-state">
      <p>Chưa có business nào.</p>
      <small style="color:var(--text-2)">LocalBusiness structured data giúp Google hiểu rõ hơn về doanh nghiệp của bạn và hiển thị rich snippets trong kết quả tìm kiếm.</small>
    </div>
  @else
    <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Tên doanh nghiệp</th>
          <th>Loại</th>
          <th>Rating</th>
          <th>Địa chỉ</th>
          <th>Trạng thái</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      @foreach($businesses as $business)
        <tr>
          <td>{{ $business->id }}</td>
          <td>
            <b>{{ $business->name }}</b>
            @if($business->is_primary)
              <span class="badge badge-primary" style="margin-left:6px;font-size:11px">PRIMARY</span>
            @endif
            @if($business->telephone)
              <div style="font-size:12px;color:var(--text-2)">{{ $business->telephone }}</div>
            @endif
          </td>
          <td>
            <span class="badge badge-secondary">{{ $business->schema_type ?? 'LocalBusiness' }}</span>
          </td>
          <td>
            @if($business->aggregate_rating)
              <div style="color:#f59e0b">
                ⭐ {{ number_format($business->aggregate_rating, 2) }}
              </div>
              <small style="color:var(--text-2);font-size:11px">({{ $business->review_count }} reviews)</small>
            @else
              <span style="color:var(--text-2);font-size:12px">—</span>
            @endif
          </td>
          <td style="max-width:300px">
            @if($business->full_address)
              <small>{{ $business->full_address }}</small>
            @else
              <span style="color:var(--text-2);font-size:12px">—</span>
            @endif
          </td>
          <td>
            @if($business->is_active)
              <span class="badge badge-success">Active</span>
            @else
              <span class="badge badge-secondary">Inactive</span>
            @endif
          </td>
          <td>
            <div class="actions">
              <a href="{{ route('admin.local-businesses.preview', $business) }}" class="btn btn-outline btn-sm" title="Preview JSON-LD">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </a>
              <a href="{{ route('admin.local-businesses.edit', $business) }}" class="btn btn-outline btn-sm" title="Chỉnh sửa">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </a>
              @if($business->google_place_id)
                <form method="post" action="{{ route('admin.local-businesses.sync-rating', $business) }}" style="display:inline">
                  @csrf
                  <button type="submit" class="btn btn-outline btn-sm" title="Sync rating từ testimonials">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
                  </button>
                </form>
              @endif
              <form method="post" action="{{ route('admin.local-businesses.destroy', $business) }}" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xóa business này?')" title="Xóa">
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
  @if($businesses->hasPages())
    <div class="pagination">
      {{ $businesses->links('vendor.pagination.admin') }}
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
.badge-primary {
  background: var(--primary, #6366f1);
  color: white;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 10px;
  font-weight: 600;
}
</style>
@endpush

@push('js')
<script>
function showImportLoading() {
  const overlay = document.getElementById('importLoadingOverlay');
  const modal = document.getElementById('importCrawlModal');
  if (overlay) overlay.style.display = 'flex';
  if (modal) modal.style.display = 'none';
  document.body.style.overflow = 'hidden';
}
</script>
@endpush
@endsection
