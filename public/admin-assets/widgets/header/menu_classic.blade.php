@php
  $s = $block->settings ?? [];
  $brandName = !empty($s['brand_name']) ? $s['brand_name'] : $siteName;
  $brandTag = !empty($s['brand_tag']) ? $s['brand_tag'] : $siteTag;
  $logo = $s['logo'] ?? null;
  $logoUrl = $logo ? (\Illuminate\Support\Str::startsWith($logo, ['http://','https://','theme-assets/']) ? asset($logo) : asset('storage/'.$logo)) : null;
  $showHotline = $s['show_hotline'] ?? true;
  $ctaText = $s['cta_text'] ?? 'Khảo sát miễn phí';
  $ctaUrl = $s['cta_url'] ?? route('contact');
  $showCta = $s['show_cta'] ?? true;
@endphp
<header class="site-header">
  <div class="container header-inner">
    <a href="{{ route('home') }}" class="brand" aria-label="{{ $brandName }}">
      @if($logoUrl)
        <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="brand-logo" style="max-height:48px;width:auto">
      @else
        <span class="brand-mark" aria-hidden="true">
          <svg width="28" height="28" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
            <path d="M8 9.5h12.5a3.5 3.5 0 010 7H13a3.5 3.5 0 000 7h11.5"/>
          </svg>
        </span>
        <span class="brand-text">
          <span class="brand-name">{{ $brandName }}</span>
          <span class="brand-tag">{{ $brandTag }}</span>
        </span>
      @endif
    </a>

    <nav class="nav" aria-label="Menu chính">
      @foreach($navItems as $navItem)
        @if($navItem->children->isNotEmpty())
          <div class="nav-item">
            <a href="{{ url($navItem->resolved_url) }}">{{ $navItem->label }}
              <svg class="caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><polyline points="6 9 12 15 18 9"/></svg>
            </a>
            <div class="nav-dropdown">
              @foreach($navItem->children as $child)
                <a href="{{ url($child->resolved_url) }}">{{ $child->label }}</a>
              @endforeach
            </div>
          </div>
        @else
          <a href="{{ url($navItem->resolved_url) }}">{{ $navItem->label }}</a>
        @endif
      @endforeach
    </nav>

    <div class="header-actions">
      @if($showHotline)
      <a class="hotline" href="tel:{{ $hotlineTel }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
        {{ $hotline }}
      </a>
      @endif
      @if($showCta)<a class="btn-primary" href="{{ $ctaUrl }}">{{ $ctaText }}</a>@endif
      <button class="burger" aria-label="Mở menu" aria-expanded="false"><span></span><span></span><span></span></button>
    </div>
  </div>
</header>
