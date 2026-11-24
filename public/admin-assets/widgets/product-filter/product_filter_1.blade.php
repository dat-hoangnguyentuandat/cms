@php
  $s = $block->settings ?? [];
  $limit = (int) ($s['limit'] ?? 8);
  $catIds = $s['category_ids'] ?? [];
  $cats = ($categories ?? collect());
  if (!empty($catIds)) {
    $cats = $cats->whereIn('id', $catIds);
  }
@endphp
<aside class="satc-sidebar-widget">
  <h3>{{ $block->title ?: 'Bộ lọc sản phẩm' }}</h3>
  <div class="satc-sidebar-links">
    @foreach($cats->take($limit) as $cat)
      <a href="{{ route('products.index', ['cat' => $cat->slug]) }}">{{ $cat->name }}</a>
    @endforeach
  </div>
</aside>
