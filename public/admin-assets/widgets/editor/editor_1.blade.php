@php
  $s = $block->settings ?? [];
  $body = $s['content'] ?? null;
@endphp
@if($body)
  <div>
    <div class="footer-title">{{ $block->title ?: 'Liên hệ' }}</div>
    <div class="footer-editor-content" style="color:rgba(255,255,255,.7);font-size:14px;line-height:1.7">{!! $body !!}</div>
  </div>
@else
<div>
  <div class="footer-title">{{ $block->title ?: 'Liên hệ' }}</div>
  <div class="footer-links contact-links">
    <span>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
      {{ $address ?? \App\Models\Setting::get('address','') }}
    </span>
    <a href="tel:{{ $hotlineTel ?? preg_replace('/\s+/', '', \App\Models\Setting::get('hotline','')) }}">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
      {{ $hotline ?? \App\Models\Setting::get('hotline','') }}
    </a>
    <a href="mailto:{{ $email ?? \App\Models\Setting::get('email','') }}">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
      {{ $email ?? \App\Models\Setting::get('email','') }}
    </a>
  </div>
  <div class="footer-social" style="margin-top:16px">
    <a href="{{ $facebook ?? \App\Models\Setting::get('facebook_url','#') }}" target="_blank" rel="noopener" class="social-btn" aria-label="Facebook">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12a10 10 0 10-11.6 9.9v-7H8v-2.9h2.4V9.7c0-2.4 1.4-3.8 3.6-3.8 1 0 2.1.2 2.1.2v2.3h-1.2c-1.2 0-1.5.7-1.5 1.5V12H16l-.4 2.9h-2.2v7A10 10 0 0022 12z"/></svg>
    </a>
    <a href="{{ $youtube ?? \App\Models\Setting::get('youtube_url','#') }}" target="_blank" rel="noopener" class="social-btn" aria-label="YouTube">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M23 7s-.2-1.6-.9-2.3c-.8-.9-1.7-.9-2.2-1C16.7 3.5 12 3.5 12 3.5h0s-4.7 0-7.9.2c-.4.1-1.4.1-2.2 1C1.2 5.4 1 7 1 7s-.2 1.9-.2 3.7v1.7C.8 14.2 1 16 1 16s.2 1.6.9 2.3c.8.9 2 .9 2.5 1 1.8.2 7.6.2 7.6.2s4.7 0 7.9-.2c.4-.1 1.4-.1 2.2-1 .7-.7.9-2.3.9-2.3s.2-1.9.2-3.7v-1.7C23.2 8.9 23 7 23 7zM10 14V8l5 3-5 3z"/></svg>
    </a>
  </div>
</div>
@endif
