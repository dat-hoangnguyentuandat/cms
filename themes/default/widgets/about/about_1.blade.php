@php
  // Default theme override of the global `about` widget — neutral blue/white
  // palette using this theme's CSS tokens (NOT the spa gold defaults).
  $s = $block->settings ?? [];
  $aboutHeading     = $s['heading'] ?? 'Về Chúng Tôi';
  $aboutSubheading  = $s['subheading'] ?? ($block->title ?: 'Đối Tác Tin Cậy Của Doanh Nghiệp');
  $aboutDescription = $s['description'] ?? \App\Models\Setting::get('about_desc', 'Chúng tôi cung cấp sản phẩm và dịch vụ chất lượng cao, đáp ứng mọi nhu cầu của khách hàng với sự tận tâm và chuyên nghiệp.');
  $aboutImageRaw    = $s['image'] ?? null;
  $aboutImage = $aboutImageRaw
    ? (\Illuminate\Support\Str::startsWith($aboutImageRaw, ['http://','https://','theme-assets/']) ? asset($aboutImageRaw) : asset('storage/'.$aboutImageRaw))
    : null;
  $ctaText = $s['cta_text'] ?? 'Tìm hiểu thêm';
  $ctaLink = $s['cta_link'] ?? route('about');
  $stats = $s['stats'] ?? [
    ['value' => '5+',     'label' => 'Năm kinh nghiệm'],
    ['value' => '500+',   'label' => 'Khách hàng'],
    ['value' => '1.000+', 'label' => 'Đơn hàng'],
    ['value' => '4.9★',   'label' => 'Đánh giá'],
  ];
  $initial = mb_strtoupper(mb_substr(strip_tags($aboutDescription) ?: 'A', 0, 1, 'UTF-8'), 'UTF-8');
@endphp

<section class="def-about section section-alt">
  <div class="container def-about-wrap">
    <div class="def-about-content">
      <span class="eyebrow">{{ $aboutHeading }}</span>
      <h2>{{ $aboutSubheading }}</h2>
      <p class="def-about-desc">{{ strip_tags($aboutDescription) }}</p>

      <div class="def-about-stats">
        @foreach($stats as $stat)
          <div class="def-stat">
            <span class="def-stat-num">{{ $stat['value'] ?? '' }}</span>
            <span class="def-stat-label">{{ $stat['label'] ?? '' }}</span>
          </div>
        @endforeach
      </div>

      <a href="{{ $ctaLink }}" class="btn btn-primary btn-lg">{{ $ctaText }}</a>
    </div>

    <div class="def-about-media">
      @if($aboutImage)
        <img src="{{ $aboutImage }}" alt="{{ $aboutSubheading }}" loading="lazy" onerror="this.style.display='none';this.parentElement.classList.add('is-placeholder')">
      @else
        <div class="def-about-ph">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.09-3.09a2 2 0 0 0-2.82 0L6 21"/></svg>
        </div>
      @endif
    </div>
  </div>
</section>

@once
<style>
.def-about{overflow:hidden}
.def-about-wrap{display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center}
.def-about-content{display:flex;flex-direction:column;gap:16px}
.def-about-content h2{font-size:34px;font-weight:800;color:var(--text);margin:0;line-height:1.2}
.def-about-desc{color:var(--text-2);font-size:16px;line-height:1.8;margin:0}
.def-about-stats{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin:8px 0}
.def-stat{background:#fff;border:1px solid var(--line);border-radius:var(--radius);padding:22px 18px;text-align:center;box-shadow:var(--shadow-sm);transition:transform .25s,box-shadow .25s}
.def-stat:hover{transform:translateY(-4px);box-shadow:var(--shadow-lg)}
.def-stat-num{display:block;font-size:30px;font-weight:800;color:var(--primary);line-height:1}
.def-stat-label{display:block;margin-top:6px;font-size:13px;color:var(--text-2)}
.def-about-content .btn{align-self:flex-start;margin-top:6px}
.def-about-media{position:relative}
.def-about-media img{width:100%;aspect-ratio:4/3.4;object-fit:cover;border-radius:var(--radius-lg);box-shadow:var(--shadow-lg);background:var(--bg-3)}
.def-about-ph,.def-about-media.is-placeholder{aspect-ratio:4/3.4;border-radius:var(--radius-lg);background:linear-gradient(135deg,var(--primary-l) 0%,var(--bg-3) 100%);display:flex;align-items:center;justify-content:center;color:var(--primary)}
.def-about-ph svg{width:64px;height:64px;opacity:.6}
@media(max-width:960px){
  .def-about-wrap{grid-template-columns:1fr;gap:32px}
  .def-about-media{order:-1}
  .def-about-content h2{font-size:28px}
}
@media(max-width:560px){
  .def-about-stats{grid-template-columns:1fr 1fr}
}
</style>
@endonce
