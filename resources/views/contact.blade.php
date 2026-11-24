@extends('layouts.app')
@section('title','Liên hệ & nhận báo giá')
@section('description','Liên hệ với chúng tôi để được tư vấn và nhận báo giá mũ bảo hiểm in logo trong 2 giờ làm việc.')
@push('css')<link rel="stylesheet" href="{{ asset('css/shop.css') }}">@endpush

@section('content')
@php
  $hotline       = \App\Models\Setting::get('hotline','0909 123 456');
  $address       = \App\Models\Setting::get('address','TP. Hồ Chí Minh');
  $email         = \App\Models\Setting::get('email','contact@mubaohiemquatang.com');
  $working_hours = \App\Models\Setting::get('working_hours','08:00 - 18:00 từ thứ 2 đến thứ 7');
  $zalo          = \App\Models\Setting::get('zalo','0909123456');
@endphp

<main class="shop-main">
  <div class="container">
    <nav class="breadcrumb">
      <a href="{{ route('home') }}">Trang chủ</a> <span>›</span>
      <span>Liên hệ</span>
    </nav>

    <header class="shop-page-head">
      <div class="section-eyebrow" style="justify-content:flex-start">Liên hệ</div>
      <h1 class="page-title" style="margin-bottom:8px">Nhận báo giá mũ bảo hiểm in logo</h1>
      <p class="shop-page-desc">Gửi yêu cầu để được tư vấn số lượng, kiểu dáng, thiết kế logo và báo giá nhanh trong 2 giờ làm việc.</p>
    </header>

    <div class="contact-grid">
      <div>
        <h3 class="contact-info-title">Thông tin liên hệ</h3>
        <p class="contact-info-desc">Liên hệ trực tiếp qua hotline/Zalo để được tư vấn nhanh nhất về đơn hàng in logo doanh nghiệp.</p>
        <div class="contact-items">
          <div class="contact-item">
            <div class="contact-item-icon">{!! \App\Helpers\Icon::get('location') !!}</div>
            <div>
              <div class="contact-item-label">Địa chỉ</div>
              <div class="contact-item-value">{{ $address }}</div>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-item-icon">{!! \App\Helpers\Icon::get('phone') !!}</div>
            <div>
              <div class="contact-item-label">Hotline / Zalo</div>
              <div class="contact-item-value"><a href="tel:{{ preg_replace('/\s+/','',$hotline) }}">{{ $hotline }}</a></div>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-item-icon">{!! \App\Helpers\Icon::get('mail') !!}</div>
            <div>
              <div class="contact-item-label">Email</div>
              <div class="contact-item-value"><a href="mailto:{{ $email }}">{{ $email }}</a></div>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-item-icon">{!! \App\Helpers\Icon::get('clock') !!}</div>
            <div>
              <div class="contact-item-label">Giờ làm việc</div>
              <div class="contact-item-value">{{ $working_hours }}</div>
            </div>
          </div>
        </div>

        <div class="contact-quick-actions">
          <a href="tel:{{ preg_replace('/\s+/','',$hotline) }}" class="btn btn-primary">{!! \App\Helpers\Icon::get('phone') !!} Gọi hotline</a>
          <a href="https://zalo.me/{{ $zalo }}" target="_blank" rel="noopener" class="btn btn-outline-dark">{!! \App\Helpers\Icon::get('chat') !!} Chat Zalo</a>
        </div>

        <iframe class="map-embed" title="Google Maps TP.HCM" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps?q=Ho%20Chi%20Minh%20City&output=embed"></iframe>
      </div>

      <div class="contact-form-box">
        <form id="contactForm" novalidate>
          @csrf
          <div class="form-row">
            <div class="form-group">
              <label for="name">Họ và tên</label>
              <input type="text" id="name" name="name" placeholder="Nguyễn Văn A" autocomplete="name" required>
              <span class="error-msg" role="alert"></span>
            </div>
            <div class="form-group">
              <label for="phone">Số điện thoại</label>
              <input type="tel" id="phone" name="phone" placeholder="0909123456" autocomplete="tel" required>
              <span class="error-msg" role="alert"></span>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" placeholder="email@congty.com" autocomplete="email">
              <span class="error-msg" role="alert"></span>
            </div>
            <div class="form-group">
              <label for="subject">Nhu cầu</label>
              <select id="subject" name="subject">
                <option>Báo giá mũ in logo</option>
                <option>Tư vấn kiểu dáng</option>
                <option>Đặt hàng số lượng lớn</option>
                <option>Thiết kế logo</option>
                <option>Khác</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="message">Mô tả yêu cầu</label>
            <textarea id="message" name="message" placeholder="Ví dụ: Cần 200 mũ nửa đầu in logo công ty, màu đen, giao trong 2 tuần..." required></textarea>
            <span class="error-msg" role="alert"></span>
          </div>
          <button type="submit" class="btn-submit">{!! \App\Helpers\Icon::get('send') !!} Gửi yêu cầu báo giá</button>
        </form>
        <div id="formSuccess" class="form-success" role="status" aria-live="polite">
          <div class="success-icon-wrap">{!! \App\Helpers\Icon::get('check') !!}</div>
          <h4>Đã nhận yêu cầu!</h4>
          <p>Chúng tôi sẽ liên hệ báo giá trong vòng 2 giờ làm việc.</p>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection

@push('js')
<script>
const contactForm = document.getElementById('contactForm');
if (contactForm) {
  contactForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = contactForm.querySelector('.btn-submit');
    btn.disabled = true;
    try {
      const res = await fetch('{{ route("contact.store") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: new FormData(contactForm)
      });
      const data = await res.json();
      if (data.success) {
        contactForm.style.display = 'none';
        document.getElementById('formSuccess').classList.add('show');
      } else {
        btn.disabled = false;
      }
    } catch { btn.disabled = false; }
  });
}
</script>
@endpush
