@php
  $_s           = $block->settings ?? [];
  $_displayMode = $_s['display_mode'] ?? 'show';
  $_title       = $block->title ?: 'Tư vấn nhanh';
  $_subtitle    = $_s['subtitle'] ?? 'Đội ngũ chuyên gia sẵn sàng đồng hành và hỗ trợ bạn!';
  $_subject     = $_s['subject'] ?? 'Liên hệ từ trang chủ';
  $_btnText     = $_s['btn_text'] ?? 'Gửi yêu cầu tư vấn';
  $_bgColor     = $_s['bg_color'] ?? '#0f1c3f';
  $_accentColor = $_s['accent_color'] ?? '#1683f7';
  $_reqPhone    = (bool) ($_s['req_phone'] ?? true);
  $_reqEmail    = (bool) ($_s['req_email'] ?? false);
  $_showCompany = (bool) ($_s['show_company'] ?? false);
  $_badges      = array_filter([
    $_s['badge_1'] ?? 'Tư vấn miễn phí',
    $_s['badge_2'] ?? 'Khảo sát tận nơi',
    $_s['badge_3'] ?? 'Báo giá nhanh chóng',
    $_s['badge_4'] ?? 'Hỗ trợ 24/7',
  ]);
  $_uid         = 'cf2-' . $block->id;
@endphp

@once
  @push('css')
  <style>
    .cf2-section { padding: 60px 0; }
    .cf2-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: center; }
    .cf2-title { font-size: 26px; font-weight: 700; color: #fff; line-height: 1.35; margin: 0 0 12px; }
    .cf2-sub { color: rgba(255,255,255,.7); font-size: 15px; line-height: 1.7; margin: 0 0 20px; }
    .cf2-badges { display: flex; flex-wrap: wrap; gap: 8px; }
    .cf2-badge { padding: 5px 14px; border-radius: 20px; background: rgba(255,255,255,.12); color: rgba(255,255,255,.85); font-size: 13px; }
    .cf2-form { display: flex; flex-direction: column; gap: 10px; }
    .cf2-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .cf2-input,
    .cf2-textarea { width: 100%; padding: 11px 14px; border: 1.5px solid rgba(255,255,255,.2); border-radius: 8px; font-size: 14px; color: #fff; background: rgba(255,255,255,.1); outline: none; font-family: inherit; box-sizing: border-box; transition: border-color .2s; }
    .cf2-input::placeholder,
    .cf2-textarea::placeholder { color: rgba(255,255,255,.5); }
    .cf2-input:focus,
    .cf2-textarea:focus { border-color: var(--cf2-accent); background: rgba(255,255,255,.15); }
    .cf2-textarea { resize: vertical; min-height: 90px; }
    .cf2-btn { padding: 13px; color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: opacity .2s; }
    .cf2-btn:hover { opacity: .88; }
    .cf2-msg { font-size: 14px; min-height: 18px; text-align: center; }
    .cf2-msg.success { color: #4ade80; }
    .cf2-msg.error { color: #f87171; }
    @media (max-width: 768px) {
      .cf2-grid { grid-template-columns: 1fr; gap: 32px; }
      .cf2-row { grid-template-columns: 1fr; }
    }
  </style>
  @endpush

  @push('js')
  <script>
  (function(){
    document.querySelectorAll('[data-cf2-form]').forEach(function(form){
      form.addEventListener('submit', function(e){
        e.preventDefault();
        var btn = form.querySelector('[data-cf2-btn]');
        var msg = form.querySelector('[data-cf2-msg]');
        var orig = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Đang gửi...';
        msg.className = 'cf2-msg';
        msg.textContent = '';
        fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r){ return r.json(); })
        .then(function(data){
          if (data.success) {
            msg.className = 'cf2-msg success';
            msg.textContent = data.message || 'Gửi thành công! Chúng tôi sẽ liên hệ sớm.';
            form.reset();
          } else {
            msg.className = 'cf2-msg error';
            var errs = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
            msg.textContent = errs;
          }
        })
        .catch(function(){
          msg.className = 'cf2-msg error';
          msg.textContent = 'Có lỗi xảy ra, vui lòng thử lại.';
        })
        .finally(function(){
          btn.disabled = false;
          btn.textContent = orig;
        });
      });
    });
  })();
  </script>
  @endpush
@endonce

@if($_displayMode !== 'hide')
<section class="cf2-section" id="{{ $_uid }}" style="background:{{ $_bgColor }};--cf2-accent:{{ $_accentColor }}">
  <div class="container">
    <div class="cf2-grid">

      <div>
        <h2 class="cf2-title">{{ $_title }}</h2>
        @if($_subtitle)<p class="cf2-sub">{{ $_subtitle }}</p>@endif
        @if($_badges)
        <div class="cf2-badges">
          @foreach($_badges as $_badge)
            <span class="cf2-badge">{{ $_badge }}</span>
          @endforeach
        </div>
        @endif
      </div>

      <form class="cf2-form" method="POST" action="{{ route('contact.store') }}" data-cf2-form>
        @csrf
        <input type="hidden" name="subject" value="{{ $_subject }}">
        <div class="cf2-row">
          <input class="cf2-input" type="text" name="name" placeholder="Họ và tên *" required>
          <input class="cf2-input" type="tel" name="phone" placeholder="Số điện thoại{{ $_reqPhone ? ' *' : '' }}" @if($_reqPhone) required @endif>
        </div>
        <input class="cf2-input" type="email" name="email" placeholder="Email{{ $_reqEmail ? ' *' : '' }}" @if($_reqEmail) required @endif>
        @if($_showCompany)
        <input class="cf2-input" type="text" name="company" placeholder="Công ty / Tổ chức">
        @endif
        <textarea class="cf2-textarea" name="message" placeholder="Nhu cầu của bạn *" required></textarea>
        <button class="cf2-btn" type="submit" data-cf2-btn style="background:{{ $_accentColor }}">{{ $_btnText }}</button>
        <div class="cf2-msg" data-cf2-msg></div>
      </form>

    </div>
  </div>
</section>
@endif
