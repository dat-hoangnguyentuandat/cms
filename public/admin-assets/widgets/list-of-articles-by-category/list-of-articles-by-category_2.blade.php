@php
  use App\Models\Category;
  use App\Models\News;

  $settings = $block->settings ?? [];
  $displayMode = $settings['display_mode'] ?? 'show';
  $selectedIds = collect($settings['category_ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->values();
  $limit = max(1, (int) ($settings['limit_per_category'] ?? 6));
  $desktopItems = max(1, (int) ($settings['desktop_items'] ?? 4));
  $mobileItems = max(1, (int) ($settings['mobile_items'] ?? 2));
  $showChildren = (bool) ($settings['show_children'] ?? false);
  $showAllTab = (bool) ($settings['show_all_tab'] ?? false);
  $autoplay = (bool) ($settings['autoplay'] ?? false);
  $titleAlign = $settings['title_align'] ?? 'center';
  $showTitle = (bool) ($settings['show_title'] ?? true);
  $showCategories = (bool) ($settings['show_categories'] ?? true);
  $showCategoryImages = (bool) ($settings['show_category_images'] ?? false);
  $hideParentCategories = (bool) ($settings['hide_parent_categories'] ?? false);

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

  if ($hideParentCategories) {
      $tabCategories = $tabCategories->filter(fn ($category) => !is_null($category->parent_id))->values();
  }

  if ($tabCategories->isEmpty()) {
      $tabCategories = Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->limit(3)->get();
  }

  $categoryIds = $tabCategories->pluck('id');
  $postsByCategory = $tabCategories->mapWithKeys(function ($category) use ($limit) {
      return [$category->id => News::with('category')
          ->where('is_published', true)
          ->where('category_id', $category->id)
          ->latest()
          ->limit($limit)
          ->get()];
  });
  $allPosts = News::with('category')
      ->where('is_published', true)
      ->when($categoryIds->isNotEmpty(), fn ($q) => $q->whereIn('category_id', $categoryIds))
      ->latest()
      ->limit($limit)
      ->get();

  $uid = 'article-cat-' . $block->id;
@endphp

@if($displayMode !== 'hide')
<section class="article-category-widget" id="{{ $uid }}" style="--desktop-items:{{ $desktopItems }};--mobile-items:{{ $mobileItems }};--title-align:{{ $titleAlign }}">
  <div class="article-category-container">
    @if($showTitle)
      <div class="article-category-heading">
        @if(!empty($settings['title_url']))
          <a href="{{ $settings['title_url'] }}"><h2>{{ $block->title ?: 'SẢN PHẨM MỚI NHẤT' }}</h2></a>
        @else
          <h2>{{ $block->title ?: 'SẢN PHẨM MỚI NHẤT' }}</h2>
        @endif
        @if(!empty($settings['subtitle']))
          <div class="article-category-subtitle">{!! nl2br(e($settings['subtitle'])) !!}</div>
        @endif
      </div>
    @endif

    @if($showCategories)
    <div class="article-category-tabs" role="tablist">
      @if($showAllTab)
        <button type="button" class="active" data-target="all">Tất cả</button>
      @endif
      @foreach($tabCategories as $index => $category)
        <button type="button" class="{{ !$showAllTab && $index === 0 ? 'active' : '' }}" data-target="cat-{{ $category->id }}">
          @if($showCategoryImages && $category->image_url)
            <img src="{{ $category->image_url }}" alt="{{ $category->name }}">
          @endif
          <span>{{ $category->name }}</span>
        </button>
      @endforeach
    </div>
    @endif

    <div class="article-category-slider" data-autoplay="{{ $autoplay ? '1' : '0' }}">
      <button class="article-nav prev" type="button" aria-label="Trước">‹</button>
      <div class="article-category-panels">
        @if($showAllTab)
          <div class="article-panel active" data-panel="all">
            @forelse($allPosts as $post)
              @include('widgets.partials.article-category-card', ['post' => $post])
            @empty
              <p class="article-empty">Chưa có bài viết nào.</p>
            @endforelse
          </div>
        @endif
        @foreach($tabCategories as $index => $category)
          <div class="article-panel {{ !$showAllTab && $index === 0 ? 'active' : '' }}" data-panel="cat-{{ $category->id }}">
            @forelse($postsByCategory->get($category->id, collect()) as $post)
              @include('widgets.partials.article-category-card', ['post' => $post])
            @empty
              <p class="article-empty">Chưa có bài viết nào.</p>
            @endforelse
          </div>
        @endforeach
      </div>
      <button class="article-nav next" type="button" aria-label="Sau">›</button>
    </div>
  </div>
</section>

@once
  @push('css')
  <style>
    .article-category-widget{padding:32px 0;background:#fff}.article-category-container{max-width:1080px;margin:0 auto}.article-category-heading{text-align:var(--title-align)}.article-category-heading h2{margin:0 0 22px;color:#00853d;font-size:16px;font-weight:500;text-transform:uppercase}.article-category-heading a{text-decoration:none}.article-category-subtitle{margin:-12px 0 18px;color:#666;font-size:13px;line-height:1.6}.article-category-tabs{display:flex;gap:4px;margin-bottom:20px}.article-category-tabs button{position:relative;border:0;background:#d9efb8;color:#111;padding:12px 24px;font-size:13px;cursor:pointer;display:flex;align-items:center;gap:8px}.article-category-tabs button img{width:26px;height:26px;object-fit:contain}.article-category-tabs button.active{background:#69c900;color:#fff;font-weight:700}.article-category-tabs button.active:after{content:"";position:absolute;left:50%;bottom:-6px;transform:translateX(-50%);border-left:6px solid transparent;border-right:6px solid transparent;border-top:6px solid #69c900}.article-category-slider{position:relative}.article-category-panels{overflow:hidden}.article-panel{display:none;grid-template-columns:repeat(var(--desktop-items),minmax(0,1fr));gap:16px}.article-panel.active{display:grid}.article-card{display:flex;flex-direction:column;align-items:center;text-align:center;border:1px solid #ddd;min-height:340px;padding:28px 12px 22px;text-decoration:none;color:#111;background:#fff}.article-card-image{height:190px;width:100%;display:flex;align-items:center;justify-content:center;margin-bottom:18px}.article-card-image img{max-width:100%;max-height:100%;object-fit:contain}.article-card-title{font-size:13px;line-height:1.5;min-height:40px;margin-bottom:8px}.article-card-meta{color:#69c900;font-weight:700;font-size:12px}.article-empty{grid-column:1/-1;text-align:center;color:#777;padding:30px 0}.article-nav{position:absolute;top:50%;transform:translateY(-50%);z-index:2;width:24px;height:24px;border:0;border-radius:4px;background:#d9efb8;color:#111;font-size:24px;line-height:20px;cursor:pointer}.article-nav.prev{left:-10px}.article-nav.next{right:-10px}@media(max-width:768px){.article-category-container{padding:0 12px}.article-panel{grid-template-columns:repeat(var(--mobile-items),minmax(0,1fr))}.article-category-tabs{overflow-x:auto}.article-category-tabs button{white-space:nowrap}.article-card{min-height:280px}.article-card-image{height:150px}.article-nav.prev{left:0}.article-nav.next{right:0}}
  </style>
  @endpush
  @push('js')
  <script>
    document.addEventListener('click', function(e) {
      var tab = e.target.closest('.article-category-tabs button');
      if (!tab) return;
      var widget = tab.closest('.article-category-widget');
      widget.querySelectorAll('.article-category-tabs button').forEach(function(btn){ btn.classList.remove('active'); });
      widget.querySelectorAll('.article-panel').forEach(function(panel){ panel.classList.remove('active'); });
      tab.classList.add('active');
      widget.querySelector('[data-panel="' + tab.dataset.target + '"]')?.classList.add('active');
    });
    document.addEventListener('click', function(e) {
      var nav = e.target.closest('.article-nav');
      if (!nav) return;
      var widget = nav.closest('.article-category-widget');
      var tabs = Array.from(widget.querySelectorAll('.article-category-tabs button'));
      var current = tabs.findIndex(function(tab){ return tab.classList.contains('active'); });
      var next = nav.classList.contains('next') ? current + 1 : current - 1;
      if (next < 0) next = tabs.length - 1;
      if (next >= tabs.length) next = 0;
      tabs[next]?.click();
    });
    document.querySelectorAll('.article-category-slider[data-autoplay="1"]').forEach(function(slider) {
      setInterval(function(){ slider.querySelector('.article-nav.next')?.click(); }, 4500);
    });
  </script>
  @endpush
@endonce
@endif
