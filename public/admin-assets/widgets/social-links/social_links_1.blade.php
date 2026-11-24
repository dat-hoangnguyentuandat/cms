@php
  $s = $block->settings ?? [];
  $links = $s['items'] ?? [];
@endphp
<div>
  <div class="footer-title">{{ $block->title ?: 'Kết nối với chúng tôi' }}</div>
  <div class="footer-social" style="margin-top:12px">
    @if(!empty($links))
      @foreach($links as $link)
        <a href="{{ $link['url'] ?? '#' }}" target="_blank" rel="noopener" class="social-btn" aria-label="{{ $link['label'] ?? 'Link' }}" style="display:inline-flex;align-items:center;gap:6px">
          @if(!empty($link['label']))<span>{{ $link['label'] }}</span>@endif
        </a>
      @endforeach
    @else
    <a href="{{ $facebook ?? \App\Models\Setting::get('facebook_url','#') }}" target="_blank" rel="noopener" class="social-btn" aria-label="Facebook">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12a10 10 0 10-11.6 9.9v-7H8v-2.9h2.4V9.7c0-2.4 1.4-3.8 3.6-3.8 1 0 2.1.2 2.1.2v2.3h-1.2c-1.2 0-1.5.7-1.5 1.5V12H16l-.4 2.9h-2.2v7A10 10 0 0022 12z"/></svg>
    </a>
    <a href="{{ $youtube ?? \App\Models\Setting::get('youtube_url','#') }}" target="_blank" rel="noopener" class="social-btn" aria-label="YouTube">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M23 7s-.2-1.6-.9-2.3c-.8-.9-1.7-.9-2.2-1C16.7 3.5 12 3.5 12 3.5h0s-4.7 0-7.9.2c-.4.1-1.4.1-2.2 1C1.2 5.4 1 7 1 7s-.2 1.9-.2 3.7v1.7C.8 14.2 1 16 1 16s.2 1.6.9 2.3c.8.9 2 .9 2.5 1 1.8.2 7.6.2 7.6.2s4.7 0 7.9-.2c.4-.1 1.4-.1 2.2-1 .7-.7.9-2.3.9-2.3s.2-1.9.2-3.7v-1.7C23.2 8.9 23 7 23 7zM10 14V8l5 3-5 3z"/></svg>
    </a>
    @if($zalo ?? \App\Models\Setting::get('zalo',''))
    <a href="https://zalo.me/{{ $zalo ?? \App\Models\Setting::get('zalo','') }}" target="_blank" rel="noopener" class="social-btn" aria-label="Zalo">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.5 2 2 5.9 2 10.7c0 2.7 1.5 5.1 3.8 6.7-.1.7-.5 2-.5 2.3 0 .3.2.5.5.4.4-.2 2.4-1.5 3-1.9 1 .2 2.1.4 3.2.4 5.5 0 10-3.9 10-8.7C22 5.9 17.5 2 12 2z"/></svg>
    </a>
    @endif
    @endif
  </div>
</div>
