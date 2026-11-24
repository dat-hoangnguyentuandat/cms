@php
  $s = $block->settings ?? [];
  $desc = $s['desc'] ?? 'Theo dõi chúng tôi để cập nhật tin tức mới nhất.';
  $fbUrl = !empty($s['fb_url']) ? $s['fb_url'] : ($facebook ?? \App\Models\Setting::get('facebook_url','#'));
  $btnText = $s['btn_text'] ?? 'Theo dõi Fanpage';
  $embed = $s['embed'] ?? null;
@endphp
<div class="footer-fanpage">
  <div class="footer-title">{{ $block->title ?: 'Fanpage' }}</div>
  @if($embed)
    <div class="footer-fanpage-embed" style="margin-top:12px">{!! $embed !!}</div>
  @else
    <p style="color:rgba(255,255,255,.65);font-size:13px;margin-bottom:12px">{{ $desc }}</p>
    <a href="{{ $fbUrl }}" target="_blank" rel="noopener" class="social-btn" aria-label="Facebook" style="display:inline-flex;align-items:center;gap:8px;padding:8px 16px;background:rgba(255,255,255,.08);border-radius:6px;color:#fff;font-size:13px">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12a10 10 0 10-11.6 9.9v-7H8v-2.9h2.4V9.7c0-2.4 1.4-3.8 3.6-3.8 1 0 2.1.2 2.1.2v2.3h-1.2c-1.2 0-1.5.7-1.5 1.5V12H16l-.4 2.9h-2.2v7A10 10 0 0022 12z"/></svg>
      {{ $btnText }}
    </a>
  @endif
</div>
