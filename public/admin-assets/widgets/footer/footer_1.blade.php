@php
  $s = $block->settings ?? [];
  $copyright = $s['copyright'] ?? ('© '.date('Y').' '.($siteName ?? \App\Models\Setting::get('site_name','')).' - All rights reserved.');
  $links = $s['items'] ?? [];
@endphp
<div class="footer-bottom">
  <span>{{ $copyright }}</span>
  <span>
    @if(!empty($links))
      @foreach($links as $link)
        <a href="{{ $link['url'] ?? '#' }}">{{ $link['label'] ?? '' }}</a>
      @endforeach
    @else
      <a href="{{ route('contact') }}">Sitemap</a>
      <a href="{{ route('contact') }}">Điều khoản sử dụng</a>
      <a href="{{ route('contact') }}">Chính sách bảo mật</a>
    @endif
  </span>
</div>
