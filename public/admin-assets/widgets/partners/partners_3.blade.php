@php
  $_s           = $block->settings ?? [];
  $_displayMode = $_s['display_mode'] ?? 'show';
  $_title       = $block->title ?: 'Đối tác chiến lược';
  $_titleUrl    = $_s['title_url'] ?? '';
  $_subtitle    = $_s['subtitle'] ?? '';
  $_layout      = $_s['layout'] ?? 'marquee';
  $_bgColor     = $_s['bg_color'] ?? '#ffffff';
  $_cols        = (int) ($_s['desktop_cols'] ?? 5);
  $_showName    = (bool) ($_s['show_name'] ?? false);
  $_autoplay    = (bool) ($_s['autoplay'] ?? true);
  $_speed       = max(10, (int) ($_s['speed'] ?? 40));
  $_items       = array_values(array_filter($_s['items'] ?? [], fn($i) => !empty($i['logo'])));
  $_uid         = 'pt3-' . $block->id;
@endphp

@once
  @push('css')
  <style>
    .pt3-section { padding: 48px 0; }
    .pt3-head { text-align: center; margin-bottom: 32px; }
    .pt3-head-title { font-size: 26px; font-weight: 700; color: #1a2340; margin: 0 0 8px; line-height: 1.3; }
    .pt3-head-title a { color: inherit; text-decoration: none; }
    .pt3-head-sub { color: #6b7280; font-size: 14px; margin: 0; }
    /* Grid layout */
    .pt3-grid { display: grid; gap: 16px; }
    .pt3-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
    .pt3-grid.cols-3 { grid-template-columns: repeat(3, 1fr); }
    .pt3-grid.cols-4 { grid-template-columns: repeat(4, 1fr); }
    .pt3-grid.cols-5 { grid-template-columns: repeat(5, 1fr); }
    .pt3-grid.cols-6 { grid-template-columns: repeat(6, 1fr); }
    .pt3-card { border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; background: #fff; transition: box-shadow .2s, border-color .2s; }
    .pt3-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); border-color: #d1d5db; }
    .pt3-card a { display: flex; flex-direction: column; align-items: center; gap: 8px; text-decoration: none; }
    .pt3-logo { max-height: 52px; width: auto; max-width: 100%; object-fit: contain; display: block; filter: grayscale(30%); transition: filter .2s; }
    .pt3-card:hover .pt3-logo { filter: none; }
    .pt3-name { font-size: 12px; color: #6b7280; text-align: center; }
    /* Marquee layout */
    .pt3-marquee-wrap { overflow: hidden; position: relative; }
    .pt3-marquee-wrap::before,
    .pt3-marquee-wrap::after { content: ''; position: absolute; top: 0; bottom: 0; width: 80px; z-index: 2; pointer-events: none; }
    .pt3-marquee-wrap::before { left: 0; background: linear-gradient(to right, var(--pt3-bg), transparent); }
    .pt3-marquee-wrap::after  { right: 0; background: linear-gradient(to left, var(--pt3-bg), transparent); }
    .pt3-marquee-track { display: flex; gap: 24px; width: max-content; }
    .pt3-marquee-track.running { animation: pt3-scroll linear infinite; }
    .pt3-marquee-track:hover { animation-play-state: paused; }
    @keyframes pt3-scroll { from { transform: translateX(0); } to { transform: translateX(-50%); } }
    .pt3-marquee-item { flex-shrink: 0; border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px 24px; background: #fff; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; min-width: 140px; transition: box-shadow .2s; }
    .pt3-marquee-item:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }
    .pt3-marquee-item a { display: flex; flex-direction: column; align-items: center; gap: 6px; text-decoration: none; }
    @media (max-width: 768px) {
      .pt3-grid.cols-5, .pt3-grid.cols-6 { grid-template-columns: repeat(3, 1fr); }
      .pt3-grid.cols-4 { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 480px) {
      .pt3-grid.cols-3, .pt3-grid.cols-4, .pt3-grid.cols-5, .pt3-grid.cols-6 { grid-template-columns: repeat(2, 1fr); }
    }
  </style>
  @endpush
@endonce

@if($_displayMode !== 'hide' && count($_items) > 0)
<section class="pt3-section" id="{{ $_uid }}" style="background:{{ $_bgColor }};--pt3-bg:{{ $_bgColor }}">
  <div class="container">

    @if($_title || $_subtitle)
    <div class="pt3-head">
      @if($_title)
      <h2 class="pt3-head-title">
        @if($_titleUrl)<a href="{{ $_titleUrl }}">{{ $_title }}</a>@else{{ $_title }}@endif
      </h2>
      @endif
      @if($_subtitle)<p class="pt3-head-sub">{{ $_subtitle }}</p>@endif
    </div>
    @endif

    @if($_layout === 'marquee')
    @php
      if ($_autoplay) {
        // Repeat until base has at least 8, then double for seamless loop
        $_base = $_items;
        while (count($_base) < 8) { $_base = array_merge($_base, $_items); }
        $_loopItems = array_merge($_base, $_base);
        $_duration  = count($_base) * (110 - $_speed);
      } else {
        $_loopItems = $_items;
        $_duration  = 0;
      }
    @endphp
    <div class="pt3-marquee-wrap" style="{{ !$_autoplay ? 'overflow:visible' : '' }}">
      <div class="pt3-marquee-track {{ $_autoplay ? 'running' : '' }}" style="{{ $_autoplay ? 'animation-duration:' . $_duration . 's' : 'flex-wrap:wrap;width:auto' }}">
        @foreach($_loopItems as $_item)
        <div class="pt3-marquee-item">
          @if(!empty($_item['url']))<a href="{{ $_item['url'] }}" target="_blank" rel="noopener">@endif
          <img class="pt3-logo" src="{{ asset('storage/' . $_item['logo']) }}" alt="{{ $_item['name'] ?? 'Đối tác' }}" loading="lazy">
          @if($_showName && !empty($_item['name']))<span class="pt3-name">{{ $_item['name'] }}</span>@endif
          @if(!empty($_item['url']))</a>@endif
        </div>
        @endforeach
      </div>
    </div>

    @else
    <div class="pt3-grid cols-{{ $_cols }}">
      @foreach($_items as $_item)
      <div class="pt3-card">
        @if(!empty($_item['url']))<a href="{{ $_item['url'] }}" target="_blank" rel="noopener">@endif
        <img class="pt3-logo" src="{{ asset('storage/' . $_item['logo']) }}" alt="{{ $_item['name'] ?? 'Đối tác' }}" loading="lazy">
        @if($_showName && !empty($_item['name']))<span class="pt3-name">{{ $_item['name'] }}</span>@endif
        @if(!empty($_item['url']))</a>@endif
      </div>
      @endforeach
    </div>
    @endif

  </div>
</section>
@endif
