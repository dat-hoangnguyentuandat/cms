@php
  $settings = $block->settings ?? [];
  $statsFromSettings = $settings['items'] ?? ($settings['stats'] ?? []);
  $backgroundImage = $settings['background_image'] ?? null;
  $bgUrl = null;
  if ($backgroundImage) {
    $bgUrl = \Illuminate\Support\Str::startsWith($backgroundImage, ['http://','https://','theme-assets/'])
      ? asset($backgroundImage)
      : asset('storage/'.$backgroundImage);
  }

  if (!empty($statsFromSettings)) {
    $stats = $statsFromSettings;
  } else {
    $stats = [
      ['num'=>'10+',   'label'=>'Năm kinh nghiệm'],
      ['num'=>'500+',  'label'=>'Khách hàng tin tưởng'],
      ['num'=>'1000+', 'label'=>'Dự án đã triển khai'],
      ['num'=>'30+',   'label'=>'Kỹ thuật viên'],
      ['num'=>'24/7',  'label'=>'Hỗ trợ kỹ thuật'],
      ['num'=>'100%',  'label'=>'Thiết bị chính hãng'],
    ];
  }
@endphp
<section class="stats" @if($bgUrl) style="background-image: url('{{ $bgUrl }}'); background-size: cover; background-position: center; position: relative;" @endif>
  @if($bgUrl)<div style="position: absolute; inset: 0; background: rgba(0, 0, 0, 0.6);"></div>@endif
  <div class="container" style="position: relative; z-index: 1;">
    @if(!empty($settings['subtitle']))<p class="stats-sub" style="text-align:center;margin-bottom:20px;@if($bgUrl)color:#e5e7eb;@endif">{{ $settings['subtitle'] }}</p>@endif
    <div class="stats-grid">
      @foreach($stats as $stat)
        <div class="stat">
          <span class="num" @if($bgUrl) style="color: #fff;" @endif>{{ $stat['num'] ?? '' }}</span>
          <span class="label" @if($bgUrl) style="color: #e5e7eb;" @endif>{{ $stat['label'] ?? '' }}</span>
        </div>
      @endforeach
    </div>
  </div>
</section>
