<section class="section satc-solutions">
  <div class="container">
    @php $s = $block->settings ?? []; $svLimit = (int) ($s['limit'] ?? 6); @endphp
    <div class="section-head fade-in">
      @if(!empty($s['title_url']))
        <h2><a href="{{ $s['title_url'] }}">{{ $block->title ?: 'Giải pháp toàn diện cho doanh nghiệp' }}</a></h2>
      @else
        <h2>{{ $block->title ?: 'Giải pháp toàn diện cho doanh nghiệp' }}</h2>
      @endif
      @if(!empty($s['subtitle']))<p class="section-sub">{{ $s['subtitle'] }}</p>@endif
      <div class="subline"></div>
    </div>
    <div class="solutions-grid fade-in">
      @forelse(($services ?? collect())->take($svLimit) as $sv)
        <a class="solution-card" href="{{ route('service.show', $sv->slug) }}">
          <div class="img" style="background-image:url('{{ $sv->image_url }}')"></div>
          <div class="body">
            <h3>{{ $sv->name }}</h3>
            <ul>
              @foreach(array_slice(array_filter(array_map('trim', explode("\n", strip_tags(preg_replace('/<\/li>/', "\n", str_replace(['<ul>','</ul>'], '', (string) $sv->short_desc)))))), 0, 4) as $bullet)
                <li>{{ $bullet }}</li>
              @endforeach
            </ul>
            <span class="more">Xem chi tiết →</span>
          </div>
        </a>
      @empty
        <p style="grid-column:1/-1;text-align:center;color:var(--ink-500);">Chưa có giải pháp nào.</p>
      @endforelse
    </div>
  </div>
</section>
