@php
  use App\Models\Category;
  use App\Models\Product;

  $settings = $block->settings ?? [];
  $displayMode = $settings['display_mode'] ?? 'show';
  $selectedIds = collect($settings['category_ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->values();
  $limit = max(1, (int) ($settings['limit_per_category'] ?? 6));
  $desktopItems = max(1, (int) ($settings['desktop_items'] ?? 4));
  $mobileItems = max(1, (int) ($settings['mobile_items'] ?? 2));
  $showChildren = (bool) ($settings['show_children'] ?? false);
  $groupByCategory = (bool) ($settings['group_by_category'] ?? true);
  $showAllTab = (bool) ($settings['show_all_tab'] ?? false);
  $autoplay = (bool) ($settings['autoplay'] ?? false);

  $tabCategories = Category::where('is_active', true)
      ->when($selectedIds->isNotEmpty(), fn ($q) => $q->whereIn('id', $selectedIds))
      ->orderBy('sort_order')
      ->orderBy('name')
      ->get();

  if ($showChildren && $selectedIds->isNotEmpty()) {
      $childCategories = Category::where('is_active', true)
          ->whereIn('parent_id', $selectedIds)
          ->orderBy('sort_order')
          ->orderBy('name')
          ->get();
      $tabCategories = $tabCategories->merge($childCategories)->unique('id')->values();
  }

  if ($tabCategories->isEmpty()) {
      $tabCategories = Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->limit(3)->get();
  }

  $categoryIds = $tabCategories->pluck('id');
  $productsByCategory = $tabCategories->mapWithKeys(function ($category) use ($limit) {
      return [$category->id => Product::with('category')
          ->where('is_active', true)
          ->where('category_id', $category->id)
          ->latest()
          ->limit($limit)
          ->get()];
  });
  $allProducts = Product::with('category')
      ->where('is_active', true)
      ->when($categoryIds->isNotEmpty(), fn ($q) => $q->whereIn('category_id', $categoryIds))
      ->latest()
      ->limit($limit)
      ->get();

  $uid = 'product-list-' . $block->id;
@endphp

@if($displayMode !== 'hide')
<section class="product-category-widget" id="{{ $uid }}" style="--desktop-items:{{ $desktopItems }};--mobile-items:{{ $mobileItems }}">
  <div class="product-category-container">
    <div class="product-category-heading">
      @if(!empty($settings['title_url']))
        <a href="{{ $settings['title_url'] }}"><h2>{{ $block->title ?: 'SẢN PHẨM MỚI NHẤT' }}</h2></a>
      @else
        <h2>{{ $block->title ?: 'SẢN PHẨM MỚI NHẤT' }}</h2>
      @endif
      @if(!empty($settings['subtitle']))
        <div class="product-category-subtitle">{!! nl2br(e($settings['subtitle'])) !!}</div>
      @endif
    </div>

    <div class="product-category-tabs" role="tablist">
      @if($showAllTab)
        <button type="button" class="active" data-target="all">Tất cả</button>
      @endif
      @foreach($tabCategories as $index => $category)
        <button type="button" class="{{ !$showAllTab && $index === 0 ? 'active' : '' }}" data-target="cat-{{ $category->id }}">{{ $category->name }}</button>
      @endforeach
    </div>

    <div class="product-category-slider" data-autoplay="{{ $autoplay ? '1' : '0' }}" data-grouped="{{ $groupByCategory ? '1' : '0' }}">
      <button class="product-nav prev" type="button" aria-label="Trước">‹</button>
      <div class="product-category-panels">
        @if($showAllTab)
          <div class="product-panel active" data-panel="all">
            @forelse($allProducts as $product)
              @include('widgets.partials.product-category-card', ['product' => $product])
            @empty
              <p class="product-empty">Chưa có sản phẩm nào.</p>
            @endforelse
          </div>
        @endif
        @foreach($tabCategories as $index => $category)
          <div class="product-panel {{ !$showAllTab && $index === 0 ? 'active' : '' }}" data-panel="cat-{{ $category->id }}">
            @php $panelProducts = $groupByCategory ? $productsByCategory->get($category->id, collect()) : $allProducts; @endphp
            @forelse($panelProducts as $product)
              @include('widgets.partials.product-category-card', ['product' => $product])
            @empty
              <p class="product-empty">Chưa có sản phẩm nào.</p>
            @endforelse
          </div>
        @endforeach
      </div>
      <button class="product-nav next" type="button" aria-label="Sau">›</button>
    </div>
  </div>
</section>

@once
  @push('css')
  <style>
    .product-category-widget{padding:32px 0;background:#fff}.product-category-container{max-width:1080px;margin:0 auto}.product-category-heading h2{margin:0 0 22px;color:#00853d;font-size:16px;font-weight:500;text-transform:uppercase}.product-category-heading a{text-decoration:none}.product-category-subtitle{margin:-12px 0 18px;color:#666;font-size:13px;line-height:1.6}.product-category-tabs{display:flex;gap:4px;margin-bottom:20px}.product-category-tabs button{position:relative;border:0;background:#d9efb8;color:#111;padding:12px 24px;font-size:13px;cursor:pointer}.product-category-tabs button.active{background:#69c900;color:#fff;font-weight:700}.product-category-tabs button.active:after{content:"";position:absolute;left:50%;bottom:-6px;transform:translateX(-50%);border-left:6px solid transparent;border-right:6px solid transparent;border-top:6px solid #69c900}.product-category-slider{position:relative}.product-category-panels{overflow:hidden}.product-panel{display:none;grid-template-columns:repeat(var(--desktop-items),minmax(0,1fr));gap:16px}.product-panel.active{display:grid}.product-card{display:flex;flex-direction:column;align-items:center;text-align:center;border:1px solid #ddd;min-height:340px;padding:28px 12px 22px;text-decoration:none;color:#111;background:#fff}.product-card-image{height:190px;width:100%;display:flex;align-items:center;justify-content:center;margin-bottom:18px}.product-card-image img{max-width:100%;max-height:100%;object-fit:contain}.product-card-title{font-size:13px;line-height:1.5;min-height:40px;margin-bottom:8px}.product-card-price{color:#69c900;font-weight:700;font-size:12px}.product-card-old-price{color:#999;text-decoration:line-through;font-weight:400;margin-left:6px}.product-empty{grid-column:1/-1;text-align:center;color:#777;padding:30px 0}.product-nav{position:absolute;top:50%;transform:translateY(-50%);z-index:2;width:24px;height:24px;border:0;border-radius:4px;background:#d9efb8;color:#111;font-size:24px;line-height:20px;cursor:pointer}.product-nav.prev{left:-10px}.product-nav.next{right:-10px}@media(max-width:768px){.product-category-container{padding:0 12px}.product-panel{grid-template-columns:repeat(var(--mobile-items),minmax(0,1fr))}.product-category-tabs{overflow-x:auto}.product-category-tabs button{white-space:nowrap}.product-card{min-height:280px}.product-card-image{height:150px}.product-nav.prev{left:0}.product-nav.next{right:0}}
  </style>
  @endpush
  @push('js')
  <script>
    document.addEventListener('click', function(e) {
      var tab = e.target.closest('.product-category-tabs button');
      if (!tab) return;
      var widget = tab.closest('.product-category-widget');
      widget.querySelectorAll('.product-category-tabs button').forEach(function(btn){ btn.classList.remove('active'); });
      widget.querySelectorAll('.product-panel').forEach(function(panel){ panel.classList.remove('active'); });
      tab.classList.add('active');
      widget.querySelector('[data-panel="' + tab.dataset.target + '"]')?.classList.add('active');
    });
    document.addEventListener('click', function(e) {
      var nav = e.target.closest('.product-nav');
      if (!nav) return;
      var widget = nav.closest('.product-category-widget');
      var tabs = Array.from(widget.querySelectorAll('.product-category-tabs button'));
      var current = tabs.findIndex(function(tab){ return tab.classList.contains('active'); });
      var next = nav.classList.contains('next') ? current + 1 : current - 1;
      if (next < 0) next = tabs.length - 1;
      if (next >= tabs.length) next = 0;
      tabs[next]?.click();
    });
    document.querySelectorAll('.product-category-slider[data-autoplay="1"]').forEach(function(slider) {
      setInterval(function(){ slider.querySelector('.product-nav.next')?.click(); }, 4500);
    });
  </script>
  @endpush
@endonce
@endif
