@php
  $settings    = $block->settings ?? [];
  $displayMode = $settings['display_mode'] ?? 'show';
  $images      = $settings['images'] ?? [];
  $contentSource = $settings['content_source'] ?? 'manual';
  $sourceType  = $settings['source_type'] ?? 'product';
  $sourceLimit = max(1, min(6, (int) ($settings['source_limit'] ?? 6)));
  $width       = (int) ($settings['width'] ?? 0);
  $height      = (int) ($settings['height'] ?? 0);
  $titleUrl    = $settings['title_url'] ?? '';
  $subtitle    = $settings['subtitle'] ?? '';
  $titleSize   = (int) ($settings['title_size'] ?? 16);
  $titleAlign  = $settings['title_align'] ?? 'center';
  $assetUrl = function ($path) {
      $path = trim((string) $path);
      if ($path === '') return '';
      if (preg_match('/^https?:\/\//i', $path) || str_starts_with($path, '/')) return $path;
      if (str_starts_with($path, 'theme-assets/')) return asset($path);
      return asset('storage/' . $path);
  };
  if ($contentSource === 'import') {
      if ($sourceType === 'service') {
          $images = \App\Models\Service::where('is_active', true)->orderByDesc('is_featured')->orderByDesc('id')->limit($sourceLimit)->get()->pluck('image_url')->all();
      } elseif ($sourceType === 'news') {
          $images = \App\Models\News::where('is_published', true)->orderByDesc('id')->limit($sourceLimit)->get()->pluck('image_url')->all();
      } else {
          $images = \App\Models\Product::where('is_active', true)->orderByDesc('is_featured')->orderByDesc('id')->limit($sourceLimit)->get()->pluck('image_url')->all();
      }
  }
  $imgStyle    = ($width || $height)
      ? 'width:' . ($width ?: 'auto') . 'px;height:' . ($height ?: 'auto') . 'px;object-fit:contain'
      : 'max-width:100%;height:auto';
@endphp

@if($displayMode !== 'hide')
<section class="banner8-widget">
  <div class="banner8-container">
    @if($block->title)
      <div class="banner8-heading" style="text-align:{{ $titleAlign }}">
        @if($titleUrl)
          <a href="{{ $titleUrl }}"><h2 style="font-size:{{ $titleSize }}px">{{ $block->title }}</h2></a>
        @else
          <h2 style="font-size:{{ $titleSize }}px">{{ $block->title }}</h2>
        @endif
      </div>
    @endif
    @if($subtitle)
      <div class="banner8-subtitle">{!! nl2br(e($subtitle)) !!}</div>
    @endif
    <div class="banner8-images">
      @foreach($images as $imgPath)
        @if($imgPath)
          <div class="banner8-item">
            <img src="{{ $assetUrl($imgPath) }}" alt="" style="{{ $imgStyle }}" loading="lazy">
          </div>
        @endif
      @endforeach
    </div>
  </div>
</section>

@once
  @push('css')
  <style>
    .banner8-widget{padding:24px 0;background:#fff}
    .banner8-container{max-width:1080px;margin:0 auto;padding:0 16px}
    .banner8-heading{text-align:center;margin-bottom:12px}
    .banner8-heading h2{font-size:16px;font-weight:600;text-transform:uppercase;color:#111;margin:0}
    .banner8-heading a{text-decoration:none;color:inherit}
    .banner8-subtitle{text-align:center;color:#666;font-size:13px;margin-bottom:16px}
    .banner8-images{display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:16px}
    .banner8-item{display:flex;align-items:center;justify-content:center}
    .banner8-item img{display:block}
    @media(max-width:768px){.banner8-images{gap:10px}.banner8-item img{max-width:120px}}
  </style>
  @endpush
@endonce
@endif
