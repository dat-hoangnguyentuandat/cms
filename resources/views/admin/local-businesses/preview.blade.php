@extends('layouts.admin')
@section('title', 'Preview Structured Data - ' . $localBusiness->name)

@section('content')
<div class="card">
  <div class="card-header">
    <h3>Preview Structured Data</h3>
    <div style="display:flex;gap:8px">
      <a href="{{ route('admin.local-businesses.edit', $localBusiness) }}" class="btn btn-outline btn-sm">Chỉnh sửa</a>
      <a href="{{ route('admin.local-businesses.index') }}" class="btn btn-outline btn-sm">← Quay lại</a>
    </div>
  </div>

  <div style="padding:0 24px 24px">
    <h4 style="margin:0 0 8px">{{ $localBusiness->name }}</h4>
    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:24px">
      <span class="badge badge-primary">{{ $localBusiness->schema_type }}</span>
      @if($localBusiness->is_primary)
        <span class="badge" style="background:#10b981;color:white">PRIMARY</span>
      @endif
      @if($localBusiness->is_active)
        <span class="badge badge-success">Active</span>
      @else
        <span class="badge badge-secondary">Inactive</span>
      @endif
    </div>

    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin-bottom:24px">
      <h5 style="margin:0 0 12px;font-size:14px;color:var(--text-2)">Thông tin tóm tắt</h5>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;font-size:14px">
        @if($localBusiness->telephone)
          <div>
            <strong>📞 Điện thoại:</strong><br>
            {{ $localBusiness->telephone }}
          </div>
        @endif
        @if($localBusiness->email)
          <div>
            <strong>✉️ Email:</strong><br>
            {{ $localBusiness->email }}
          </div>
        @endif
        @if($localBusiness->full_address)
          <div>
            <strong>📍 Địa chỉ:</strong><br>
            {{ $localBusiness->full_address }}
          </div>
        @endif
        @if($localBusiness->aggregate_rating)
          <div>
            <strong>⭐ Rating:</strong><br>
            {{ number_format($localBusiness->aggregate_rating, 2) }} / 5 ({{ $localBusiness->review_count }} reviews)
          </div>
        @endif
      </div>
    </div>

    <h4 style="margin:24px 0 12px">JSON-LD Structured Data</h4>
    <p style="color:var(--text-2);font-size:14px;margin-bottom:12px">
      Đây là structured data sẽ được inject vào <code>&lt;head&gt;</code> của website.
      Copy và test tại <a href="https://search.google.com/test/rich-results" target="_blank" style="color:var(--primary)">Google Rich Results Test</a>.
    </p>

    <div style="position:relative">
      <button onclick="copyJsonLd()" class="btn btn-outline btn-sm" style="position:absolute;top:12px;right:12px;z-index:1">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
          <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
          <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
        </svg>
        Copy
      </button>
      <pre id="jsonLdContent" style="background:#1e293b;color:#e2e8f0;padding:20px;border-radius:8px;overflow-x:auto;font-size:13px;line-height:1.6;margin:0"><code>{{ json_encode($jsonLd, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></pre>
    </div>

    <h4 style="margin:32px 0 12px">HTML Script Tag</h4>
    <p style="color:var(--text-2);font-size:14px;margin-bottom:12px">
      Script tag đầy đủ để inject vào HTML:
    </p>

    <div style="position:relative">
      <button onclick="copyScriptTag()" class="btn btn-outline btn-sm" style="position:absolute;top:12px;right:12px;z-index:1">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
          <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
          <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
        </svg>
        Copy
      </button>
      <pre id="scriptTagContent" style="background:#f1f5f9;color:#334155;padding:20px;border-radius:8px;overflow-x:auto;font-size:13px;line-height:1.6;margin:0"><code>{{ htmlspecialchars($scriptTag) }}</code></pre>
    </div>

    <div style="background:#fef3c7;border:1px solid #fbbf24;border-radius:8px;padding:16px;margin-top:24px">
      <h5 style="margin:0 0 8px;color:#92400e">💡 Lưu ý</h5>
      <ul style="margin:8px 0 0;padding-left:20px;color:#92400e;font-size:14px">
        <li>Structured data đã được tự động inject vào website nếu business này là <strong>PRIMARY</strong> và <strong>ACTIVE</strong>.</li>
        <li>Kiểm tra tại <a href="https://search.google.com/test/rich-results" target="_blank" style="color:#92400e;text-decoration:underline">Google Rich Results Test</a>.</li>
        <li>Google có thể mất vài ngày để index structured data mới.</li>
        <li>Đảm bảo thông tin chính xác để tránh bị Google penalty.</li>
      </ul>
    </div>
  </div>
</div>

@push('css')
<style>
code {
  font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
}
.badge-primary {
  background: var(--primary, #6366f1);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}
</style>
@endpush

@push('js')
<script>
function copyJsonLd() {
  const content = document.getElementById('jsonLdContent').textContent;
  navigator.clipboard.writeText(content).then(() => {
    alert('✅ Đã copy JSON-LD vào clipboard!');
  }).catch(() => {
    alert('❌ Không thể copy. Vui lòng copy thủ công.');
  });
}

function copyScriptTag() {
  const content = document.getElementById('scriptTagContent').textContent;
  navigator.clipboard.writeText(content).then(() => {
    alert('✅ Đã copy script tag vào clipboard!');
  }).catch(() => {
    alert('❌ Không thể copy. Vui lòng copy thủ công.');
  });
}
</script>
@endpush
@endsection
