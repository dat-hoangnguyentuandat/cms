@extends('layouts.admin')
@section('title','Theme')

@section('content')
<div class="card">
  <div class="card-header">
    <h3>Cài đặt theme</h3>
    @if($canManagePackages)
      <form method="post" action="{{ route('admin.themes.upload') }}" enctype="multipart/form-data">
        @csrf
        <input type="file" name="theme" id="theme-file-input" accept=".zip" required hidden onchange="this.form.submit()">
        <label for="theme-file-input" class="btn btn-primary" style="cursor:pointer">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          Tải theme (.zip)
        </label>
      </form>
    @endif
  </div>

  @include('admin.partials.list-filters', [
    'clearUrl' => route('admin.themes.index'),
    'hasActiveFilters' => filled($q) || filled($status) || filled($from) || filled($to),
    'fields' => [
      ['type' => 'text', 'name' => 'q', 'value' => $q, 'placeholder' => 'Tìm tên theme...'],
      ['type' => 'select', 'name' => 'status', 'value' => $status, 'label' => 'Trạng thái', 'options' => ['1' => 'Đang dùng', '0' => 'Chưa dùng']],
      ['type' => 'date', 'name' => 'from', 'value' => $from, 'placeholder' => 'Từ ngày'],
      ['type' => 'date', 'name' => 'to', 'value' => $to, 'placeholder' => 'Đến ngày'],
    ],
  ])

  @if($themes->isEmpty())
    <div class="empty-state">
      <p>Chưa có theme nào được cài đặt.</p>
      <p style="color:#64748b;font-size:13px;margin-top:8px">Tải lên file <code>.zip</code> chứa <code>theme.json</code>, thư mục <code>views/</code> và <code>assets/</code>.</p>
    </div>
  @else
  <div class="themes-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px;padding:18px">
    @foreach($themes as $t)
      <article class="theme-card" style="border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.06);display:flex;flex-direction:column">
        <div style="aspect-ratio:16/9;background:#f1f5f9;display:flex;align-items:center;justify-content:center;overflow:hidden">
          @if(!empty($t['preview']))
            <img src="{{ asset('theme-assets/'.$t['name'].'/'.ltrim($t['preview'], 'assets/')) }}" alt="{{ $t['name'] }}" style="width:100%;height:100%;object-fit:cover">
          @else
            <svg viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" width="48" height="48"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
          @endif
        </div>
        <div style="padding:14px 16px;display:flex;flex-direction:column;gap:6px;flex:1">
          <div style="display:flex;align-items:center;gap:8px;justify-content:space-between">
            <h4 style="margin:0;font-size:15px">{{ $t['name'] === 'default' ? 'Mặc định' : $t['name'] }}</h4>
            @if($t['is_active'])
              <span class="badge badge-success">Đang dùng</span>
            @endif
          </div>
          <div style="margin-top:auto;display:flex;gap:8px;padding-top:10px;border-top:1px dashed #e2e8f0">
            @if(!$t['is_active'])
              <form method="post" action="{{ route('admin.themes.activate', $t['name']) }}" style="flex:1">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm" style="width:100%">Kích hoạt</button>
              </form>
              @if($canManagePackages)
                <form method="post" action="{{ route('admin.themes.destroy', $t['name']) }}" onsubmit="return confirm('Xóa theme {{ $t['name'] === 'default' ? 'Mặc định' : $t['name'] }}?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm" {{ $t['name'] === 'default' ? 'disabled' : '' }}>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                  </button>
                </form>
              @endif
            @else
              <a href="{{ route('home') }}" target="_blank" class="btn btn-outline btn-sm" style="flex:1;text-align:center">Xem website</a>
            @endif
            <a href="{{ route('home', ['theme' => $t['name']]) }}" target="_blank" class="btn btn-outline btn-sm" title="Xem thử">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </a>
          </div>
        </div>
      </article>
    @endforeach
  </div>
  @endif
</div>

@if(session('preview_theme') && session('preview_theme') !== $active)
<div class="alert alert-info" style="margin-top:14px">
  Bạn đang preview theme <b>{{ session('preview_theme') === 'default' ? 'Mặc định' : session('preview_theme') }}</b>.
  <a href="{{ route('admin.themes.index', ['theme' => $active]) }}">Trở lại theme đang dùng ({{ $active === 'default' ? 'Mặc định' : $active }})</a>.
</div>
@endif
@endsection
