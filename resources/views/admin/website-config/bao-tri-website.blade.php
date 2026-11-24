@extends('layouts.admin')
@section('title','Bảo trì website')

@section('content')
<div class="wc-layout">
  @include('admin.website-config._sidebar')

  <div class="wc-content">
    <form method="post" action="{{ route('admin.website-config.maintenance.update') }}">
      @csrf

      <div class="card">
        <div class="card-header">
          <h3>Bảo trì website</h3>
          @php $isOn = ($s['maintenance_mode'] ?? '0') === '1'; @endphp
          <span class="badge {{ $isOn ? 'badge-danger' : 'badge-success' }}">
            {{ $isOn ? 'Đang bảo trì' : 'Hoạt động bình thường' }}
          </span>
        </div>

        <div class="form-group">
          <label class="form-check" style="gap:12px;cursor:pointer">
            <input type="hidden" name="settings[maintenance_mode]" value="0">
            <input type="checkbox" name="settings[maintenance_mode]" value="1"
              id="maintenance_toggle"
              {{ $isOn ? 'checked' : '' }}
              style="width:20px;height:20px;cursor:pointer;accent-color:var(--danger)">
            <span style="font-weight:600;font-size:15px">Bật chế độ bảo trì</span>
          </label>
          <div class="form-help" style="margin-top:6px">
            Khi bật, toàn bộ khách truy cập sẽ thấy trang bảo trì. Admin đã đăng nhập vẫn xem được website bình thường.
          </div>
        </div>

        <div class="form-group" style="margin-top:8px">
          <label>Thông báo bảo trì</label>
          <textarea name="settings[maintenance_message]" class="form-control rich-editor" rows="8">{{ $s['maintenance_message'] ?? '<p>Hiện tại, cửa hàng đang trong quá trình bảo trì trang web.</p><p>Hãy cho những điều bất ngờ của chúng tôi nhé.</p><p>Hân hạnh</p>' }}</textarea>
          <div class="form-help">Nội dung hiển thị cho khách khi website đang bảo trì. Hỗ trợ định dạng HTML.</div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary">Lưu cấu hình</button>
    </form>
  </div>
</div>

@push('js')
<script>
  const toggle = document.getElementById('maintenance_toggle');
  const badge  = toggle.closest('.card').querySelector('.badge');
  toggle.addEventListener('change', function() {
    if (this.checked) {
      badge.textContent = 'Đang bảo trì';
      badge.className   = 'badge badge-danger';
    } else {
      badge.textContent = 'Hoạt động bình thường';
      badge.className   = 'badge badge-success';
    }
  });
</script>
@endpush
@endsection
