@php
  $settings = $block->settings ?? [];
  $slidesFromSettings = $settings['items'] ?? ($settings['slides'] ?? []);
  $resolveImg = function ($path) {
    if (empty($path)) return asset('theme-assets/default/images/banner.jpg');
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
    if (str_starts_with($path, 'theme-assets/')) return asset($path);
    return asset('storage/' . $path);
  };

  if (!empty($slidesFromSettings)) {
    $slides = array_map(function($slide) use ($resolveImg) {
      return [
        'image' => $resolveImg($slide['image'] ?? null),
        'heading' => $slide['heading'] ?? \App\Models\Setting::get('site_name', 'Site Name'),
        'sub' => $slide['sub'] ?? \App\Models\Setting::get('site_tagline', 'Tagline'),
        'url' => $slide['url'] ?? '',
      ];
    }, $slidesFromSettings);
  } else {
    $slides = [
      [
        'image' => asset('theme-assets/satc/images/banner.jpg'),
        'heading' => \App\Models\Setting::get('site_name','SATC.VN'),
        'sub' => \App\Models\Setting::get('site_tagline','Giải pháp công nghệ & an ninh toàn diện'),
        'url' => '',
      ],
      [
        'image' => asset('theme-assets/satc/images/about.jpg'),
        'heading' => \App\Models\Setting::get('site_name','SATC.VN'),
        'sub' => \App\Models\Setting::get('hero_lead','Đồng hành cùng doanh nghiệp trong kỷ nguyên số'),
        'url' => '',
      ],
    ];
  }
  $sliderAutoplay = ($settings['autoplay'] ?? true) ? 1 : 0;
@endphp
<section class="widget-slider" id="slider-{{ $block->id ?? 'main' }}" data-autoplay="{{ $sliderAutoplay }}">
  <div class="slider-track">
    @foreach($slides as $i => $slide)
      <div class="slider-slide {{ $i === 0 ? 'active' : '' }}" style="background-image:url('{{ $slide['image'] }}')">
        <div class="slider-overlay"></div>
        <div class="slider-content">
          <span class="slider-brand">{{ $slide['heading'] }}</span>
          <p class="slider-sub">{{ $slide['sub'] }}</p>
          @if(!empty($slide['url']))<a class="slider-link" href="{{ $slide['url'] }}" aria-label="{{ $slide['heading'] }}"></a>@endif
        </div>
      </div>
    @endforeach
  </div>
  <button class="slider-nav prev" aria-label="Trước">&#8249;</button>
  <button class="slider-nav next" aria-label="Sau">&#8250;</button>
  <div class="slider-dots">
    @foreach($slides as $i => $slide)
      <span class="slider-dot {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}"></span>
    @endforeach
  </div>
</section>
<script>
(function(){
  var el = document.getElementById('slider-{{ $block->id ?? 'main' }}');
  if (!el) return;
  var slides = el.querySelectorAll('.slider-slide');
  var dots   = el.querySelectorAll('.slider-dot');
  var cur = 0, timer;
  var useAutoplay = el.getAttribute('data-autoplay') === '1';
  function go(n) {
    slides[cur].classList.remove('active');
    dots[cur].classList.remove('active');
    cur = (n + slides.length) % slides.length;
    slides[cur].classList.add('active');
    dots[cur].classList.add('active');
  }
  function autoplay() { if (useAutoplay) timer = setInterval(() => go(cur + 1), 5000); }
  el.querySelector('.prev').addEventListener('click', () => { clearInterval(timer); go(cur - 1); autoplay(); });
  el.querySelector('.next').addEventListener('click', () => { clearInterval(timer); go(cur + 1); autoplay(); });
  dots.forEach((d, i) => d.addEventListener('click', () => { clearInterval(timer); go(i); autoplay(); }));
  autoplay();
})();
</script>
