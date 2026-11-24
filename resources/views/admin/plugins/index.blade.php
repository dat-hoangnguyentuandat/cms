@extends('layouts.admin')
@section('title','Plugins')

@section('content')
<div class="wc-layout">
  @include('admin.plugins._sidebar')

  <div class="wc-content">
<div class="card">
  <div class="card-header">
    <h3>Quản lý plugin</h3>
    @if($canManagePackages)
      <form method="post" action="{{ route('admin.plugins.upload') }}" enctype="multipart/form-data">
      @csrf
      <input type="file" name="plugin" id="plugin-file-input" accept=".zip" required hidden onchange="this.form.submit()">
      <label for="plugin-file-input" class="btn btn-primary" style="cursor:pointer">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Cài / cập nhật plugin (.zip)
      </label>
      </form>
    @endif
  </div>

  @include('admin.partials.list-filters', [
    'clearUrl' => route('admin.plugins.index'),
    'hasActiveFilters' => filled($q) || filled($status),
    'fields' => [
      ['type' => 'text', 'name' => 'q', 'value' => $q, 'placeholder' => 'Tìm plugin...'],
      ['type' => 'select', 'name' => 'status', 'value' => $status, 'label' => 'Trạng thái', 'options' => ['1' => 'Đang bật', '0' => 'Đang tắt']],
    ],
  ])

  @if($plugins->isEmpty())
    <div class="empty-state">
      <p>Chưa có plugin nào được cài đặt.</p>
      <p style="color:#64748b;font-size:13px;margin-top:8px">Tải lên file <code>.zip</code> chứa <code>plugin.json</code>.</p>
    </div>
  @else
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Plugin</th>
            <th>Phiên bản</th>
            <th>Tác giả</th>
            <th>Trạng thái</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($plugins as $plugin)
            <tr>
              <td>
                <b>{{ $plugin['title'] }}</b>
                <div style="color:#64748b;font-size:13px;margin-top:3px">{{ $plugin['name'] }}</div>
                @if($plugin['description'])
                  <div style="color:#475569;font-size:13px;margin-top:6px;max-width:520px">{{ $plugin['description'] }}</div>
                @endif
              </td>
              <td>{{ $plugin['version'] }}</td>
              <td>{{ $plugin['author'] ?: 'Unknown' }}</td>
              <td>
                @if($plugin['is_active'])
                  <span class="badge badge-success">Đang bật</span>
                @else
                  <span class="badge badge-secondary">Đang tắt</span>
                @endif
              </td>
              <td>
                <div class="actions">
                  @if($plugin['has_settings'])
                    @php
                      $settingsRoute = $plugin['settings_route'] ?? null;
                      $settingsUrl = ($settingsRoute && \Illuminate\Support\Facades\Route::has($settingsRoute))
                        ? route($settingsRoute)
                        : route('admin.plugins.settings', $plugin['name']);
                    @endphp
                    <a href="{{ $settingsUrl }}" class="btn btn-outline btn-sm">Cấu hình</a>
                  @endif
                  @if($plugin['is_active'])
                    <form method="post" action="{{ route('admin.plugins.deactivate', $plugin['name']) }}" style="display:inline">
                      @csrf
                      <button type="submit" class="btn btn-outline btn-sm">Tắt</button>
                    </form>
                  @else
                    <form method="post" action="{{ route('admin.plugins.activate', $plugin['name']) }}" style="display:inline">
                      @csrf
                      <button type="submit" class="btn btn-primary btn-sm">Bật</button>
                    </form>
                    @if($canManagePackages)
                      <form method="post" action="{{ route('admin.plugins.destroy', $plugin['name']) }}" style="display:inline" onsubmit="return confirm('Gỡ plugin {{ $plugin['title'] }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                        </button>
                      </form>
                    @endif
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>
  </div>
</div>
@endsection
