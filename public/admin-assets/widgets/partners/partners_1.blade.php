<section class="partners-band">
  <div class="container">
    <div class="section-head fade-in">
      <h2>{{ $block->title ?: 'Đối tác chiến lược' }}</h2>
      <div class="subline"></div>
    </div>
    <div class="partners-grid fade-in">
      @foreach(['hikvision','dahua','ruijie','tplink','grandstream','yeastar','dell','hp','microsoft'] as $logo)
        <div class="partner-card">
          <img src="{{ asset('theme-assets/satc/images/partners/' . $logo . '.svg') }}" alt="{{ strtoupper($logo) }}" style="max-height:40px;width:auto;object-fit:contain;">
        </div>
      @endforeach
    </div>
  </div>
</section>
