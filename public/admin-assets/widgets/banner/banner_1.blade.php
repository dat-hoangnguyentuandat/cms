@php
  $settings = $block->settings ?? [];
  $image = $settings['image'] ?? null;
  $width = (int) ($settings['width'] ?? 0);
  $height = (int) ($settings['height'] ?? 0);
  $radius = (int) ($settings['radius'] ?? 0);
  $fullscreen = (bool) ($settings['fullscreen'] ?? false);
  $displayMode = $settings['display_mode'] ?? 'show';
  $contentSource = $settings['content_source'] ?? 'manual';
  $sourceType = $settings['source_type'] ?? 'product';
  $sourceLimit = max(1, min(6, (int) ($settings['source_limit'] ?? 1)));
  $linkUrl = '';
  $assetUrl = function ($path) {
    $path = trim((string) $path);
    if ($path === '') return '';
    if (preg_match('/^https?:\/\//i', $path) || str_starts_with($path, '/')) return $path;
    if (str_starts_with($path, 'theme-assets/')) return asset($path);
    return asset('storage/' . $path);
  };
  if ($contentSource === 'import') {
    if ($sourceType === 'service') {
      $item = \App\Models\Service::where('is_active', true)->orderByDesc('is_featured')->orderByDesc('id')->limit($sourceLimit)->first();
      $imageUrl = $item?->image_url ?: asset('theme-assets/satc/images/banner.jpg');
      $linkUrl = $item?->slug ? route('service.show', $item->slug) : '';
    } elseif ($sourceType === 'news') {
      $item = \App\Models\News::where('is_published', true)->orderByDesc('id')->limit($sourceLimit)->first();
      $imageUrl = $item?->image_url ?: asset('theme-assets/satc/images/banner.jpg');
      $linkUrl = $item?->slug ? route('news.show', $item->slug) : '';
    } else {
      $item = \App\Models\Product::where('is_active', true)->orderByDesc('is_featured')->orderByDesc('id')->limit($sourceLimit)->first();
      $imageUrl = $item?->image_url ?: asset('theme-assets/satc/images/banner.jpg');
      $linkUrl = $item?->slug ? route('product.show', $item->slug) : '';
    }
  } else {
    $imageUrl = $image ? $assetUrl($image) : asset('theme-assets/satc/images/banner.jpg');
  }
  $imageStyle = 'width:' . ($width > 0 ? $width . 'px' : '100%') . ';height:' . ($height > 0 ? $height . 'px' : 'auto') . ';max-height:none;object-fit:' . ($height > 0 ? 'cover' : 'contain') . ';display:block';
@endphp

@if($displayMode === 'show')
  <section class="section">
    <div class="{{ $fullscreen ? '' : 'container' }}">
      <div class="fade-in">
        <div style="padding:0;overflow:hidden;border-radius:{{ $radius }}px">
          @if($linkUrl)
            <a href="{{ $linkUrl }}"><img src="{{ $imageUrl }}" alt="{{ $block->title ?: 'Banner' }}" style="{{ $imageStyle }}"></a>
          @else
            <img src="{{ $imageUrl }}" alt="{{ $block->title ?: 'Banner' }}" style="{{ $imageStyle }}">
          @endif
        </div>
      </div>
    </div>
  </section>
@endif
