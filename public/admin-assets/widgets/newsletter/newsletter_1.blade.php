<section class="cta-band">
  <div class="container">
    @php
      $s = $block->settings ?? [];
      $subtitle = $s['subtitle'] ?? 'Đội ngũ chuyên gia sẵn sàng đồng hành và hỗ trợ bạn!';
      $btnText = $s['btn_text'] ?? 'Gửi yêu cầu tư vấn';
      $subject = $s['subject'] ?? 'Yêu cầu tư vấn từ trang chủ';
      $badges = $s['items'] ?? [];
      if (empty($badges)) {
        $badges = [
          ['text'=>'Tư vấn miễn phí'],
          ['text'=>'Khảo sát tận nơi'],
          ['text'=>'Báo giá nhanh chóng'],
          ['text'=>'Hỗ trợ 24/7'],
        ];
      }
    @endphp
    <div class="cta-band-grid">
      <div class="fade-in">
        <h2>{{ $block->title ?: 'Bạn cần tư vấn giải pháp phù hợp cho doanh nghiệp?' }}</h2>
        <p>{{ $subtitle }}</p>
        <div class="badges">
          @foreach($badges as $badge)
            <span>{{ $badge['text'] ?? '' }}</span>
          @endforeach
        </div>
      </div>
      <form class="cta-form fade-in" method="POST" action="{{ route('contact.store') }}" data-contact>
        @csrf
        <div class="row">
          <input class="form-input" type="text" name="name" placeholder="Họ và tên" required>
          <input class="form-input" type="tel" name="phone" placeholder="Số điện thoại" required>
        </div>
        <input class="form-input" type="email" name="email" placeholder="Email">
        <input type="hidden" name="subject" value="{{ $subject }}">
        <textarea class="form-textarea" name="message" placeholder="Nhu cầu của bạn" required></textarea>
        <button class="btn-primary" type="submit">{{ $btnText }}</button>
        <div class="form-msg"></div>
      </form>
    </div>
  </div>
</section>
