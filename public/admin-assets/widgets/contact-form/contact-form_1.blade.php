@php
  $_s           = $block->settings ?? [];
  $_displayMode = $_s['display_mode'] ?? 'show';
  $_layout      = $_s['layout'] ?? 'two_col';
  $_title       = $block->title ?: 'Liên hệ với chúng tôi';
  $_subtitle    = $_s['subtitle'] ?? 'Hãy để lại thông tin, chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất.';
  $_subject     = $_s['subject'] ?? 'Liên hệ từ trang chủ';
  $_titleUrl    = $_s['title_url'] ?? '';
  $_btnText     = $_s['btn_text'] ?? 'Gửi liên hệ';
  $_bgColor     = $_s['bg_color'] ?? '#f8fafc';
  $_accentColor = $_s['accent_color'] ?? '#1683f7';
  $_showPhone   = (bool) ($_s['show_phone'] ?? true);
  $_showEmail   = (bool) ($_s['show_email'] ?? true);
  $_showAddress = (bool) ($_s['show_address'] ?? true);
  $_reqPhone    = (bool) ($_s['req_phone'] ?? true);
  $_reqEmail    = (bool) ($_s['req_email'] ?? false);
  $_showCompany = (bool) ($_s['show_company'] ?? false);
  $_uid         = 'cf1-' . $block->id;
@endphp

@once
  @push('css')
  <style>
    .cf1-section { padding: 60px 0; }
    .cf1-inner { display: grid; gap: 48px; align-items: start; }
    .cf1-inner.cf1-two-col { grid-template-columns: 1fr 1fr; }
    .cf1-inner.cf1-form-only,
    .cf1-inner.cf1-form-center { grid-template-columns: 1fr; max-width: 680px; margin: 0 auto; }
    .cf1-info-title { font-size: 28px; font-weight: 700; color: #1a2340; margin: 0 0 12px; line-height: 1.3; }
    .cf1-info-title a { color: inherit; text-decoration: none; }
    .cf1-info-sub { color: #6b7280; font-size: 15px; line-height: 1.7; margin: 0 0 28px; }
    .cf1-info-items { display: flex; flex-direction: column; gap: 16px; }
    .cf1-info-item { display: flex; align-items: flex-start; gap: 14px; }
    .cf1-info-icon { width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .cf1-info-icon svg { width: 20px; height: 20px; stroke: #fff; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .cf1-info-label { font-size: 12px; color: #9ca3af; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 2px; }
    .cf1-info-value { font-size: 15px; color: #1a2340; font-weight: 500; }
    .cf1-info-value a { color: inherit; text-decoration: none; }
    .cf1-info-value a:hover { opacity: .8; }
    .cf1-form-wrap { background: #fff; border-radius: 16px; padding: 36px; box-shadow: 0 4px 24px rgba(0,0,0,.07); }
    .cf1-form-title { font-size: 20px; font-weight: 700; color: #1a2340; margin: 0 0 20px; }
    .cf1-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .cf1-field { margin-bottom: 14px; }
    .cf1-field input,
    .cf1-field textarea { width: 100%; padding: 11px 14px; border: 1.5px solid #e5e7eb; border-radius: 8px; font-size: 14px; color: #1a2340; background: #f9fafb; transition: border-color .2s; outline: none; font-family: inherit; box-sizing: border-box; }
    .cf1-field input:focus,
    .cf1-field textarea:focus { border-color: var(--cf1-accent); background: #fff; }
    .cf1-field textarea { resize: vertical; min-height: 110px; }
    .cf1-btn { width: 100%; padding: 13px; color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: opacity .2s; }
    .cf1-btn:hover { opacity: .88; }
    .cf1-msg { margin-top: 10px; font-size: 14px; min-height: 20px; text-align: center; }
    .cf1-msg.success { color: #16a34a; }
    .cf1-msg.error { color: #dc2626; }
    .cf1-center-info { display: flex; flex-wrap: wrap; gap: 24px; margin-top: 32px; justify-content: center; }
    @media (max-width: 768px) {
      .cf1-inner.cf1-two-col { grid-template-columns: 1fr; gap: 32px; }
      .cf1-row { grid-template-columns: 1fr; }
      .cf1-form-wrap { padding: 24px 18px; }
    }
  </style>
  @endpush

  @push('js')
  <script>
  (function(){
    document.querySelectorAll('[data-cf1-form]').forEach(function(form){
      form.addEventListener('submit', function(e){
        e.preventDefault();
        var btn = form.querySelector('[data-cf1-btn]');
        var msg = form.querySelector('[data-cf1-msg]');
        var orig = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Đang gửi...';
        msg.className = 'cf1-msg';
        msg.textContent = '';
        fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r){ return r.json(); })
        .then(function(data){
          if (data.success) {
            msg.className = 'cf1-msg success';
            msg.textContent = data.message || 'Gửi thành công! Chúng tôi sẽ liên hệ sớm.';
            form.reset();
          } else {
            msg.className = 'cf1-msg error';
            var errs = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
            msg.textContent = errs;
          }
        })
        .catch(function(){
          msg.className = 'cf1-msg error';
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
<section class="cf1-section" id="{{ $_uid }}" style="background:{{ $_bgColor }};--cf1-accent:{{ $_accentColor }}">
  <div class="container">

    @if($_layout === 'form_center')
      {{-- Form giữa trang, info bên dưới --}}
      <div class="cf1-inner cf1-form-center">
        <div>
          @if($_title)
          <h2 class="cf1-info-title" style="text-align:center">
            @if($_titleUrl)<a href="{{ $_titleUrl }}">{{ $_title }}</a>@else{{ $_title }}@endif
          </h2>
          @endif
          @if($_subtitle)<p class="cf1-info-sub" style="text-align:center">{{ $_subtitle }}</p>@endif
        </div>
        <div class="cf1-form-wrap">
          @include('widgets._cf1_form', compact('_subject','_btnText','_accentColor','_reqPhone','_reqEmail','_showCompany'))
        </div>
        @if($_showPhone || $_showEmail || $_showAddress)
        <div class="cf1-center-info">
          @if($_showPhone && $hotline)
          <div class="cf1-info-item">
            <div class="cf1-info-icon" style="background:{{ $_accentColor }}">
              <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.91a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </div>
            <div><div class="cf1-info-label">Hotline</div><div class="cf1-info-value"><a href="tel:{{ $hotlineTel }}">{{ $hotline }}</a></div></div>
          </div>
          @endif
          @if($_showEmail && $email)
          <div class="cf1-info-item">
            <div class="cf1-info-icon" style="background:{{ $_accentColor }}">
              <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>
            <div><div class="cf1-info-label">Email</div><div class="cf1-info-value"><a href="mailto:{{ $email }}">{{ $email }}</a></div></div>
          </div>
          @endif
          @if($_showAddress && $address)
          <div class="cf1-info-item">
            <div class="cf1-info-icon" style="background:{{ $_accentColor }}">
              <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div><div class="cf1-info-label">Địa chỉ</div><div class="cf1-info-value">{{ $address }}</div></div>
          </div>
          @endif
        </div>
        @endif
      </div>

    @elseif($_layout === 'form_only')
      {{-- Chỉ form --}}
      <div class="cf1-inner cf1-form-only">
        @if($_title || $_subtitle)
        <div style="text-align:center">
          @if($_title)<h2 class="cf1-info-title">@if($_titleUrl)<a href="{{ $_titleUrl }}">{{ $_title }}</a>@else{{ $_title }}@endif</h2>@endif
          @if($_subtitle)<p class="cf1-info-sub">{{ $_subtitle }}</p>@endif
        </div>
        @endif
        <div class="cf1-form-wrap">
          @php
            // inline form to avoid @include path issues in widget render context
          @endphp
          <form method="POST" action="{{ route('contact.store') }}" data-cf1-form>
            @csrf
            <input type="hidden" name="subject" value="{{ $_subject }}">
            <div class="cf1-row">
              <div class="cf1-field"><input type="text" name="name" placeholder="Họ và tên *" required></div>
              <div class="cf1-field"><input type="tel" name="phone" placeholder="Số điện thoại{{ $_reqPhone ? ' *' : '' }}" @if($_reqPhone) required @endif></div>
            </div>
            <div class="cf1-field"><input type="email" name="email" placeholder="Email{{ $_reqEmail ? ' *' : '' }}" @if($_reqEmail) required @endif></div>
            @if($_showCompany)
            <div class="cf1-field"><input type="text" name="company" placeholder="Công ty / Tổ chức"></div>
            @endif
            <div class="cf1-field"><textarea name="message" placeholder="Nội dung *" required></textarea></div>
            <button class="cf1-btn" type="submit" data-cf1-btn style="background:{{ $_accentColor }}">{{ $_btnText }}</button>
            <div class="cf1-msg" data-cf1-msg></div>
          </form>
        </div>
      </div>

    @else
      {{-- Mặc định: 2 cột --}}
      <div class="cf1-inner cf1-two-col">
        <div class="cf1-info">
          <h2 class="cf1-info-title">
            @if($_titleUrl)<a href="{{ $_titleUrl }}">{{ $_title }}</a>@else{{ $_title }}@endif
          </h2>
          @if($_subtitle)<p class="cf1-info-sub">{{ $_subtitle }}</p>@endif
          @if($_showPhone || $_showEmail || $_showAddress)
          <div class="cf1-info-items">
            @if($_showPhone && $hotline)
            <div class="cf1-info-item">
              <div class="cf1-info-icon" style="background:{{ $_accentColor }}">
                <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.91a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
              </div>
              <div><div class="cf1-info-label">Hotline</div><div class="cf1-info-value"><a href="tel:{{ $hotlineTel }}">{{ $hotline }}</a></div></div>
            </div>
            @endif
            @if($_showEmail && $email)
            <div class="cf1-info-item">
              <div class="cf1-info-icon" style="background:{{ $_accentColor }}">
                <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              </div>
              <div><div class="cf1-info-label">Email</div><div class="cf1-info-value"><a href="mailto:{{ $email }}">{{ $email }}</a></div></div>
            </div>
            @endif
            @if($_showAddress && $address)
            <div class="cf1-info-item">
              <div class="cf1-info-icon" style="background:{{ $_accentColor }}">
                <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              </div>
              <div><div class="cf1-info-label">Địa chỉ</div><div class="cf1-info-value">{{ $address }}</div></div>
            </div>
            @endif
          </div>
          @endif
        </div>
        <div class="cf1-form-wrap">
          <div class="cf1-form-title">Gửi yêu cầu</div>
          <form method="POST" action="{{ route('contact.store') }}" data-cf1-form>
            @csrf
            <input type="hidden" name="subject" value="{{ $_subject }}">
            <div class="cf1-row">
              <div class="cf1-field"><input type="text" name="name" placeholder="Họ và tên *" required></div>
              <div class="cf1-field"><input type="tel" name="phone" placeholder="Số điện thoại{{ $_reqPhone ? ' *' : '' }}" @if($_reqPhone) required @endif></div>
            </div>
            <div class="cf1-field"><input type="email" name="email" placeholder="Email{{ $_reqEmail ? ' *' : '' }}" @if($_reqEmail) required @endif></div>
            @if($_showCompany)
            <div class="cf1-field"><input type="text" name="company" placeholder="Công ty / Tổ chức"></div>
            @endif
            <div class="cf1-field"><textarea name="message" placeholder="Nội dung *" required></textarea></div>
            <button class="cf1-btn" type="submit" data-cf1-btn style="background:{{ $_accentColor }}">{{ $_btnText }}</button>
            <div class="cf1-msg" data-cf1-msg></div>
          </form>
        </div>
      </div>
    @endif

  </div>
</section>
@endif
