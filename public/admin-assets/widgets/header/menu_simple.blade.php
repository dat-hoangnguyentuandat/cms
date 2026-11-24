@php
  $s = $block->settings ?? [];
  $brandName = !empty($s['brand_name']) ? $s['brand_name'] : $siteName;
  $logo = $s['logo'] ?? null;
  $logoUrl = $logo ? (\Illuminate\Support\Str::startsWith($logo, ['http://','https://','theme-assets/']) ? asset($logo) : asset('storage/'.$logo)) : null;
@endphp
<header class="site-header site-header--simple">
  <div class="container header-inner">
    <a href="{{ route('home') }}" class="brand" aria-label="{{ $brandName }}">
      @if($logoUrl)
        <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="brand-logo" style="max-height:44px;width:auto">
      @else
        <span class="brand-name">{{ $brandName }}</span>
      @endif
    </a>

    <nav class="nav" aria-label="Menu chính">
      @foreach($navItems as $navItem)
        <a href="{{ url($navItem->resolved_url) }}">{{ $navItem->label }}</a>
      @endforeach
    </nav>

    <button class="burger" aria-label="Mở menu" aria-expanded="false"><span></span><span></span><span></span></button>
  </div>
</header>
