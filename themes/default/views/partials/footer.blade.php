@php
  /** @var string $siteName */
  /** @var string $siteDesc */
  /** @var string $hotline */
  /** @var string $email */
  /** @var string $address */
  $layoutBlocks = $layoutBlocks ?? collect();
  $footerZoneBlocks = fn(string $zone) => $layoutBlocks->get($zone, collect());
  $hotlineTel = preg_replace('/\s+/', '', $hotline ?? '');
  $hours = $workingHours ?? \App\Models\Setting::get('working_hours', '');

  $hasFooterCols = collect(['footer_1','footer_2','footer_3'])
      ->contains(fn($z) => $footerZoneBlocks($z)->isNotEmpty());
@endphp
<footer class="site-footer">
  {{-- Footer top (full width zone) --}}
  @foreach($footerZoneBlocks('footer_top') as $block)
    @include('widgets.render', ['block' => $block])
  @endforeach

  <div class="container">
    <div class="footer-grid">
      {{-- Brand column is always present --}}
      <div class="footer-col footer-brand-col">
        <a href="{{ route('home') }}" class="footer-brand">
          @php $siteLogo = \App\Models\Setting::get('site_logo'); @endphp
          @if($siteLogo)
            <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="footer-logo">
          @else
            <span class="brand-mark">{{ mb_strtoupper(mb_substr($siteName, 0, 1, 'UTF-8'), 'UTF-8') }}</span>
            <span>{{ $siteName }}</span>
          @endif
        </a>
        <p class="footer-desc">{{ \Illuminate\Support\Str::limit(strip_tags($siteDesc), 160) }}</p>
        <div class="footer-contact">
          @if($address)<div class="footer-contact-row"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>{{ $address }}</div>@endif
          @if($hotline)<div class="footer-contact-row"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg><a href="tel:{{ $hotlineTel }}">{{ $hotline }}</a></div>@endif
          @if($email)<div class="footer-contact-row"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg><a href="mailto:{{ $email }}">{{ $email }}</a></div>@endif
          @if($hours)<div class="footer-contact-row"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>{{ $hours }}</div>@endif
        </div>
      </div>

      {{-- Configurable footer columns (admin widgets) with sensible fallback --}}
      @if($hasFooterCols)
        @foreach(['footer_1','footer_2','footer_3'] as $zone)
          @php $blocks = $footerZoneBlocks($zone); @endphp
          @if($blocks->isNotEmpty())
            <div class="footer-col">
              @foreach($blocks as $block)
                @include('widgets.render', ['block' => $block])
              @endforeach
            </div>
          @endif
        @endforeach
      @else
        <div class="footer-col">
          <div class="footer-title">Liên kết</div>
          <div class="footer-links">
            <a href="{{ route('home') }}">Trang chủ</a>
            <a href="{{ route('about') }}">Giới thiệu</a>
            <a href="{{ route('products.index') }}">Sản phẩm</a>
            <a href="{{ route('news.index') }}">Tin tức</a>
            <a href="{{ route('contact') }}">Liên hệ</a>
          </div>
        </div>
        <div class="footer-col">
          <div class="footer-title">Hỗ trợ</div>
          <div class="footer-links">
            <a href="{{ route('contact') }}">Liên hệ tư vấn</a>
            <a href="tel:{{ $hotlineTel }}">Hotline {{ $hotline }}</a>
            <a href="{{ route('news.index') }}">Tin tức & cẩm nang</a>
          </div>
        </div>
      @endif
    </div>
  </div>

  @php $bottomBlocks = $footerZoneBlocks('footer_bottom'); @endphp
  @if($bottomBlocks->isNotEmpty())
    @foreach($bottomBlocks as $block)
      @if($block->block_type === 'footer')
        <div class="footer-bottom"><div class="container"><span>© {{ date('Y') }} {{ $siteName }}. All rights reserved.</span></div></div>
      @else
        @include('widgets.render', ['block' => $block])
      @endif
    @endforeach
  @else
    <div class="footer-bottom"><div class="container"><span>© {{ date('Y') }} {{ $siteName }}. All rights reserved.</span></div></div>
  @endif
</footer>
