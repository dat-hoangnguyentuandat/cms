@php
  $s = $block->settings ?? [];
  $links = $s['items'] ?? [];
@endphp
<div>
  <div class="footer-title">{{ $block->title ?: 'Giải pháp' }}</div>
  <div class="footer-links">
    @if(!empty($links))
      @foreach($links as $link)
        <a href="{{ $link['url'] ?? '#' }}">{{ $link['label'] ?? '' }}</a>
      @endforeach
    @else
      @foreach(($solutionCats ?? collect()) as $sc)
        <a href="{{ route('services.index', ['cat' => $sc->slug]) }}">{{ $sc->name }}</a>
      @endforeach
      @if(($solutionCats ?? collect())->isEmpty())
        <a href="{{ route('services.index') }}">Camera giám sát</a>
        <a href="{{ route('services.index') }}">Mạng nội bộ & WiFi</a>
        <a href="{{ route('services.index') }}">Kiểm soát ra vào</a>
        <a href="{{ route('services.index') }}">Tổng đài điện thoại</a>
        <a href="{{ route('services.index') }}">Thiết bị văn phòng</a>
      @endif
    @endif
  </div>
</div>
