@php
  $_fabPhone    = $hotlineTel ?? preg_replace('/\s+/', '', \App\Models\Setting::get('hotline',''));
  $_fabZalo     = $zalo       ?? \App\Models\Setting::get('zalo','');
  $_fabEmail    = $email      ?? \App\Models\Setting::get('email','');
  $_fabSettings = $block->settings ?? [];
  $_fabSide     = ($block->zone ?? '') === 'float_left'
    ? 'left:18px;right:auto'
    : 'right:18px;left:auto';
  $_fabPos      = match($_fabSettings['position'] ?? 'middle') {
    'top'    => 'top:80px;bottom:auto;transform:none',
    'bottom' => 'bottom:22px;top:auto;transform:none',
    default  => 'top:50%;bottom:auto;transform:translateY(-50%)',
  };
@endphp

<div class="float-actions" aria-label="Liên hệ nhanh" style="position:fixed;{{ $_fabSide }};{{ $_fabPos }}">
  @if($_fabZalo)
  <a class="float-btn" href="https://zalo.me/{{ $_fabZalo }}" target="_blank" rel="noopener" aria-label="Chat Zalo">
    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C6.5 2 2 5.9 2 10.7c0 2.7 1.5 5.1 3.8 6.7-.1.7-.5 2-.5 2.3 0 .3.2.5.5.4.4-.2 2.4-1.5 3-1.9 1 .2 2.1.4 3.2.4 5.5 0 10-3.9 10-8.7C22 5.9 17.5 2 12 2z"/></svg>
    <span>Zalo</span>
  </a>
  @endif

  @if($_fabPhone)
  <a class="float-btn" href="tel:{{ $_fabPhone }}" aria-label="Gọi hotline">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
    <span>Hotline</span>
  </a>
  @endif

  @if($_fabEmail)
  <a class="float-btn" href="mailto:{{ $_fabEmail }}" aria-label="Email">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
    <span>Email</span>
  </a>
  @endif

  <a class="float-btn" href="{{ route('contact') }}" aria-label="Chat ngay">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
    <span>Chat ngay</span>
  </a>
</div>
