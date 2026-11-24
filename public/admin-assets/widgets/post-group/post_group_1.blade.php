<section class="section">
  <div class="container">
    @php $s = $block->settings ?? []; $pgLimit = (int) ($s['limit'] ?? 4); @endphp
    <div class="section-head-row fade-in">
      @if(!empty($s['title_url']))
        <h2><a href="{{ $s['title_url'] }}">{{ $block->title ?: 'Tin tức mới nhất' }}</a></h2>
      @else
        <h2>{{ $block->title ?: 'Tin tức mới nhất' }}</h2>
      @endif
      <a class="btn-ghost" href="{{ route('news.index') }}">Xem tất cả →</a>
    </div>
    @if(!empty($s['subtitle']))<p class="section-sub">{{ $s['subtitle'] }}</p>@endif
    <div class="projects-strip fade-in">
      <button class="proj-nav prev" type="button" aria-label="Trước">&#8249;</button>
      <div class="projects-grid">
        @forelse(($news ?? collect())->take($pgLimit) as $post)
          <a class="project-card" href="{{ route('news.show', $post->slug) }}" style="background-image:url('{{ $post->image_url ?? asset('theme-assets/satc/images/banner.jpg') }}')">
            <div class="body">
              <h3>{{ $post->title }}</h3>
              <p>{{ \Illuminate\Support\Str::limit(strip_tags($post->excerpt ?? ''), 70) }}</p>
            </div>
          </a>
        @empty
          <p style="grid-column:1/-1;text-align:center;color:var(--ink-500);">Chưa có bài viết nào.</p>
        @endforelse
      </div>
      <button class="proj-nav next" type="button" aria-label="Sau">&#8250;</button>
    </div>
  </div>
</section>
