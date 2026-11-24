<section class="why-choose">
  <div class="container">
    @php
      $s = $block->settings ?? [];
      $introTitle = $s['intro_title'] ?? null;
      $introDesc = $s['intro_desc'] ?? 'Đồng hành cùng doanh nghiệp xây dựng nền tảng công nghệ và an ninh vững chắc cho tương lai.';
      $btnText = $s['btn_text'] ?? 'Tìm hiểu thêm →';
      $btnUrl = $s['btn_url'] ?? route('about');
      $cards = $s['items'] ?? [];
      if (empty($cards)) {
        $cards = [
          ['title'=>'Tư vấn chuyên sâu',       'desc'=>'Giải pháp phù hợp theo nhu cầu thực tế của doanh nghiệp.'],
          ['title'=>'Triển khai chuyên nghiệp', 'desc'=>'Quy trình chuẩn, đội ngũ kỹ thuật kinh nghiệm, tay nghề cao.'],
          ['title'=>'Sản phẩm chính hãng',      'desc'=>'Đa dạng thương hiệu, CO/CQ đầy đủ.'],
          ['title'=>'Bảo hành uy tín',          'desc'=>'Bảo hành chính hãng, hỗ trợ nhanh chóng.'],
          ['title'=>'Hỗ trợ 24/7',              'desc'=>'Đồng hành cùng doanh nghiệp mọi lúc, mọi nơi.'],
        ];
      }
    @endphp
    <div class="why-grid">
      <div class="why-intro fade-in">
        @if($introTitle)
          <h2>{{ $introTitle }}</h2>
        @else
          <h2>Vì sao doanh nghiệp <br><span>chọn {{ $siteName ?? \App\Models\Setting::get('site_name','') }}?</span></h2>
        @endif
        <p style="color:rgba(255,255,255,.75);margin-bottom:24px;">{{ $introDesc }}</p>
        <a class="btn-primary" href="{{ $btnUrl }}">{{ $btnText }}</a>
      </div>
      <div class="why-cards fade-in">
        @foreach($cards as $wr)
          <div class="why-card">
            <h4>{{ $wr['title'] ?? '' }}</h4>
            <p>{{ $wr['desc'] ?? '' }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</section>
