@php
  /** @var string $siteName */
  /** @var string $siteTag */
  /** @var string $hotline */
  /** @var string $hotlineTel */
@endphp
<header class="site-header">
  <div class="container header-inner">
    <a href="{{ route('home') }}" class="brand" aria-label="{{ $siteName }}">
      @php $siteLogo = \App\Models\Setting::get('site_logo'); @endphp
      @if($siteLogo)
        <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="brand-logo">
      @else
        <span class="brand-mark">{{ mb_strtoupper(mb_substr($siteName, 0, 1, 'UTF-8'), 'UTF-8') }}</span>
        <span class="brand-text">
          <span class="brand-name">{{ $siteName }}</span>
          @if($siteTag)<span class="brand-tag">{{ $siteTag }}</span>@endif
        </span>
      @endif
    </a>

    <nav class="nav" aria-label="Menu chính">
      @forelse($navItems as $navItem)
        @if($navItem->children->isNotEmpty())
          <div class="nav-item">
            <a href="{{ url($navItem->resolved_url) }}" @class(['active' => request()->is(ltrim($navItem->resolved_url, '/'))])>
              {{ $navItem->label }}
              <svg class="caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><polyline points="6 9 12 15 18 9"/></svg>
            </a>
            <div class="nav-dropdown">
              @foreach($navItem->children as $child)
                <a href="{{ url($child->resolved_url) }}">{{ $child->label }}</a>
              @endforeach
            </div>
          </div>
        @else
          <a href="{{ url($navItem->resolved_url) }}" @class(['active' => request()->is(ltrim($navItem->resolved_url, '/'))])>{{ $navItem->label }}</a>
        @endif
      @empty
        <a href="{{ route('home') }}" @class(['active' => request()->routeIs('home')])>Trang chủ</a>
        <a href="{{ route('about') }}" @class(['active' => request()->routeIs('about')])>Giới thiệu</a>
        <a href="{{ route('products.index') }}" @class(['active' => request()->routeIs('products.*') || request()->routeIs('product.*')])>Sản phẩm</a>
        <a href="{{ route('news.index') }}" @class(['active' => request()->routeIs('news.*')])>Tin tức</a>
        <a href="{{ route('contact') }}" @class(['active' => request()->routeIs('contact')])>Liên hệ</a>
      @endforelse
    </nav>

    <div class="header-actions">
      <a class="hotline" href="tel:{{ $hotlineTel }}">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
        {{ $hotline }}
      </a>
      <a class="btn btn-primary" href="{{ route('contact') }}">Liên hệ ngay</a>
      <button class="burger" aria-label="Menu" type="button"><span></span><span></span><span></span></button>
    </div>
  </div>
</header>
