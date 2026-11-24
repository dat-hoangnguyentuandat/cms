<section class="section">
  <div class="container">
    @php $s = $block->settings ?? []; $pgLimit = (int) ($s['limit'] ?? 6); @endphp
    <div class="section-head-row fade-in">
      @if(!empty($s['title_url']))
        <h2><a href="{{ $s['title_url'] }}">{{ $block->title ?: 'Sản phẩm nổi bật' }}</a></h2>
      @else
        <h2>{{ $block->title ?: 'Sản phẩm nổi bật' }}</h2>
      @endif
      <a class="btn-ghost" href="{{ route('products.index') }}">Xem tất cả →</a>
    </div>
    @if(!empty($s['subtitle']))<p class="section-sub">{{ $s['subtitle'] }}</p>@endif
    <div class="projects-strip fade-in">
      <button class="proj-nav prev" type="button" aria-label="Trước">&#8249;</button>
      <div class="projects-grid">
        @forelse(($products ?? collect())->take($pgLimit) as $p)
          <a class="project-card" href="{{ route('product.show', $p->slug) }}" style="background-image:url('{{ $p->image_url }}')">
            <div class="body">
              @if($p->category)<span class="badge">{{ $p->category->name }}</span>@endif
              <h3>{{ $p->name }}</h3>
              <p>{{ \Illuminate\Support\Str::limit($p->short_desc, 70) }}</p>
            </div>
          </a>
        @empty
          <p style="grid-column:1/-1;text-align:center;color:var(--ink-500);">Chưa có sản phẩm nào.</p>
        @endforelse
      </div>
      <button class="proj-nav next" type="button" aria-label="Sau">&#8250;</button>
    </div>
  </div>
</section>
