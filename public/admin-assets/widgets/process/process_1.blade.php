<section class="process">
  <div class="container">
    <div class="section-head fade-in">
      <h2>{{ $block->title ?: 'Quy trình triển khai' }}</h2>
      @if(!empty($block->settings['subtitle']))<p class="section-sub">{{ $block->settings['subtitle'] }}</p>@endif
      <div class="subline"></div>
    </div>
    @php
      $steps = $block->settings['items'] ?? [];
      if (empty($steps)) {
        $steps = [
          ['num'=>'01','title'=>'Tiếp nhận yêu cầu',    'desc'=>'Tiếp nhận thông tin và khảo sát nhu cầu'],
          ['num'=>'02','title'=>'Tư vấn giải pháp',      'desc'=>'Đề xuất giải pháp tối ưu & báo giá chi tiết'],
          ['num'=>'03','title'=>'Ký kết hợp đồng',       'desc'=>'Thống nhất giải pháp, tiến độ & điều khoản'],
          ['num'=>'04','title'=>'Triển khai thi công',   'desc'=>'Thi công, lắp đặt theo đúng tiêu chuẩn'],
          ['num'=>'05','title'=>'Nghiệm thu & bàn giao', 'desc'=>'Nghiệm thu, bàn giao & hướng dẫn sử dụng'],
          ['num'=>'06','title'=>'Bảo trì & hỗ trợ',     'desc'=>'Bảo trì định kỳ & hỗ trợ kỹ thuật 24/7'],
        ];
      }
    @endphp
    <div class="process-steps fade-in">
      @foreach($steps as $i => $step)
        <div class="process-step">
          <div class="num">{{ $step['num'] ?? str_pad((string)($i+1), 2, '0', STR_PAD_LEFT) }}</div>
          <h4>{{ $step['title'] ?? '' }}</h4>
          <p>{{ $step['desc'] ?? '' }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>
