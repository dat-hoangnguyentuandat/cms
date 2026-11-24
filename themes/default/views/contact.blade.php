@extends('layouts.app')
@section('title','Liên hệ')

@section('content')
@php
  $hotline       = \App\Models\Setting::get('hotline','0909 123 456');
  $address       = \App\Models\Setting::get('address','TP. Hồ Chí Minh');
  $email         = \App\Models\Setting::get('email','contact@example.com');
  $working_hours = \App\Models\Setting::get('working_hours','08:00 - 18:00 từ thứ 2 đến thứ 7');
  $zalo          = \App\Models\Setting::get('zalo','0909123456');
  $maps_embed    = \App\Models\Setting::get('maps_embed','');
@endphp

<div class="page">
  <div class="container">
    <nav class="breadcrumb"><a href="{{ route('home') }}">Trang chủ</a> · <span>Liên hệ</span></nav>
    <h1 class="page-title">Liên hệ với chúng tôi</h1>
    <p class="page-desc">Gửi yêu cầu — đội ngũ tư vấn sẽ phản hồi trong vòng 2 giờ làm việc.</p>

    <div class="contact-grid">
      <div class="contact-info">
        <div class="contact-item">
          <div class="contact-item-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          </div>
          <div>
            <div class="contact-item-label">Địa chỉ</div>
            <div class="contact-item-value">{{ $address }}</div>
          </div>
        </div>
        <div class="contact-item">
          <div class="contact-item-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          </div>
          <div>
            <div class="contact-item-label">Hotline / Zalo</div>
            <div class="contact-item-value"><a href="tel:{{ preg_replace('/\s+/','',$hotline) }}">{{ $hotline }}</a></div>
          </div>
        </div>
        <div class="contact-item">
          <div class="contact-item-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          </div>
          <div>
            <div class="contact-item-label">Email</div>
            <div class="contact-item-value"><a href="mailto:{{ $email }}">{{ $email }}</a></div>
          </div>
        </div>
        <div class="contact-item">
          <div class="contact-item-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
          </div>
          <div>
            <div class="contact-item-label">Giờ làm việc</div>
            <div class="contact-item-value">{{ $working_hours }}</div>
          </div>
        </div>
      </div>

      <form data-contact action="{{ route('contact.store') }}" method="POST" class="contact-form">
        @csrf
        <h3>Gửi yêu cầu liên hệ</h3>
        <p>Chúng tôi sẽ phản hồi qua điện thoại hoặc email trong vòng 2 giờ làm việc.</p>
        <div data-status></div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Họ tên <span class="required">*</span></label>
            <input type="text" name="name" class="form-input" required>
          </div>
          <div class="form-group">
            <label class="form-label">Số điện thoại <span class="required">*</span></label>
            <input type="tel" name="phone" class="form-input" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-input">
          </div>
          <div class="form-group">
            <label class="form-label">Tiêu đề</label>
            <input type="text" name="subject" class="form-input" placeholder="VD: Tư vấn sản phẩm">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Nội dung <span class="required">*</span></label>
          <textarea name="message" class="form-textarea" required placeholder="Mô tả nhu cầu của bạn..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-lg" style="width:100%">Gửi liên hệ</button>
      </form>
    </div>

    @if($maps_embed)
    <div class="map-wrap">
      {!! $maps_embed !!}
    </div>
    @else
    <div class="map-wrap">
      <iframe src="https://www.google.com/maps?q={{ urlencode($address) }}&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
    @endif
  </div>
</div>
@endsection
