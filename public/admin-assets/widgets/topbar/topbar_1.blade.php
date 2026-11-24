@php
  $s = $block->settings ?? [];
  $leftLabel = $s['left_label'] ?? 'Gọi ngay';
  $showAuth = $s['show_auth'] ?? true;
  $links = $s['items'] ?? [];
  $bg = $s['bg_color'] ?? null;
@endphp
<div class="site-topbar" @if($bg) style="background:{{ $bg }}" @endif>
  <div class="container topbar-inner">
    <div class="topbar-left">
      <span>{{ $leftLabel }}</span>
      <a href="tel:{{ $hotlineTel ?? preg_replace('/\s+/', '', \App\Models\Setting::get('hotline','')) }}">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
        {{ $hotline ?? \App\Models\Setting::get('hotline','') }}
      </a>
      @foreach($links as $link)
        <a href="{{ $link['url'] ?? '#' }}">{{ $link['label'] ?? '' }}</a>
      @endforeach
    </div>
    @if($showAuth)
    <div class="topbar-right">
      <a href="{{ route('admin.login') }}">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        Đăng nhập
      </a>
      <span class="topbar-sep">|</span>
      <a href="{{ route('admin.login') }}">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
        Đăng ký
      </a>
    </div>
    @endif
  </div>
</div>
