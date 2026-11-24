<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="@yield('description', strip_tags(\App\Models\Setting::get('site_desc','Chuyên sản xuất mũ bảo hiểm in logo theo yêu cầu doanh nghiệp.')))">
  <title>@yield('title', 'Trang chủ') - {{ \App\Models\Setting::get('site_name','Mũ Bảo Hiểm Quà Tặng') }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  @php
    $siteLogo = \App\Models\Setting::get('site_logo');
    $faviconUrl = $siteLogo ? asset('storage/' . $siteLogo) : asset('images/logo.png');
  @endphp
  <link rel="icon" href="{{ $faviconUrl }}">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  @stack('css')
</head>
<body>
<header id="header" role="banner">
  <div class="container">
    <div class="header-inner">
      <a href="{{ route('home') }}" class="logo" aria-label="{{ \App\Models\Setting::get('site_name') }} trang chủ">
        @php
          $headerLogo = \App\Models\Setting::get('site_logo');
          $logoUrl = $headerLogo ? asset('storage/' . $headerLogo) : asset('images/logo.png');
        @endphp
        <span class="logo-mark" aria-hidden="true"><img src="{{ $logoUrl }}" alt=""></span>
        <span>{{ \App\Models\Setting::get('site_name','Mũ Bảo Hiểm Quà Tặng') }}</span>
      </a>
      <nav class="nav-links" role="navigation" aria-label="Menu chính">
        @php
          $navItems = \App\Models\MenuItem::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();
        @endphp
        @foreach($navItems as $navItem)
          @if($navItem->children->isNotEmpty())
            <div class="nav-dropdown">
              <a href="{{ url($navItem->resolved_url) }}" class="nav-dropdown-toggle">
                {{ $navItem->label }}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><polyline points="6 9 12 15 18 9"/></svg>
              </a>
              <ul class="nav-dropdown-menu">
                @foreach($navItem->children as $child)
                  <li><a href="{{ url($child->resolved_url) }}">{{ $child->label }}</a></li>
                @endforeach
              </ul>
            </div>
          @else
            <a href="{{ url($navItem->resolved_url) }}">{{ $navItem->label }}</a>
          @endif
        @endforeach
      </nav>
      @php $hotline = \App\Models\Setting::get('hotline','0909 123 456'); @endphp
      <a href="tel:{{ preg_replace('/\s+/','',$hotline) }}" class="btn-hotline" aria-label="Gọi {{ $hotline }}">{!! \App\Helpers\Icon::get('phone') !!}{{ $hotline }}</a>
      <button class="hamburger" aria-label="Mở menu" aria-expanded="false"><span></span><span></span><span></span></button>
    </div>
  </div>
</header>

@yield('content')

@php
  $sf_name    = \App\Models\Setting::get('site_name','Mũ Bảo Hiểm Quà Tặng');
  $sf_desc    = \App\Models\Setting::get('site_desc','Chuyên sản xuất mũ bảo hiểm in logo theo yêu cầu doanh nghiệp.');
  $sf_hotline = \App\Models\Setting::get('hotline','0909 123 456');
  $sf_zalo    = \App\Models\Setting::get('zalo','0909123456');
  $sf_email   = \App\Models\Setting::get('email','contact@mubaohiemquatang.com');
  $sf_address = \App\Models\Setting::get('address','TP. Hồ Chí Minh');
  $sf_fb      = \App\Models\Setting::get('facebook_url','#');
@endphp
<footer id="footer" role="contentinfo">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <a href="{{ route('home') }}" class="logo">
          @php
            $footerLogo = \App\Models\Setting::get('site_logo');
            $footerLogoUrl = $footerLogo ? asset('storage/' . $footerLogo) : asset('images/logo.png');
          @endphp
          <span class="logo-mark"><img src="{{ $footerLogoUrl }}" alt=""></span>
          {{ $sf_name }}
        </a>
        <div class="footer-desc rich-content">{!! $sf_desc !!}</div>
        <div class="social-links">
          <a href="{{ $sf_fb }}" class="social-link" aria-label="Facebook">{!! \App\Helpers\Icon::get('facebook') !!}</a>
          <a href="https://zalo.me/{{ $sf_zalo }}" class="social-link" aria-label="Zalo">{!! \App\Helpers\Icon::get('chat') !!}</a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Menu nhanh</h4>
        <div class="footer-links">
          <a href="{{ route('about') }}">Giới thiệu</a>
          <a href="{{ route('products.index') }}">Sản phẩm</a>
          <a href="{{ route('news.index') }}">Tin tức</a>
          <a href="{{ route('contact') }}">Liên hệ</a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Chính sách</h4>
        <div class="footer-links">
          <a href="{{ route('home') }}#faq">Chính sách bảo hành</a>
          <a href="{{ route('home') }}#faq">Chính sách giao hàng</a>
          <a href="{{ route('home') }}#faq">Chính sách đổi trả</a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Liên hệ</h4>
        <div class="footer-links">
          <span>{{ $sf_address }}</span>
          <a href="tel:{{ preg_replace('/\s+/','',$sf_hotline) }}">{{ $sf_hotline }}</a>
          <a href="mailto:{{ $sf_email }}">{{ $sf_email }}</a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© {{ date('Y') }} {{ $sf_name }}. All rights reserved.</p>
      <p><a href="{{ route('admin.login') }}">Đăng nhập admin</a> · <a href="{{ route('home') }}#faq">FAQ</a></p>
    </div>
  </div>
</footer>

<div class="float-actions" aria-label="Liên hệ nhanh">
  <a href="tel:{{ preg_replace('/\s+/','',$sf_hotline) }}" class="float-item float-phone" aria-label="Gọi hotline">
    <span class="float-label">{{ $sf_hotline }}</span>
    <span class="float-circle">{!! \App\Helpers\Icon::get('phone') !!}</span>
  </a>
  <a href="https://zalo.me/{{ $sf_zalo }}" class="float-item float-zalo" target="_blank" rel="noopener" aria-label="Chat Zalo">
    <span class="float-label">Chat Zalo</span>
    <span class="float-circle">{!! \App\Helpers\Icon::get('chat') !!}</span>
  </a>
</div>
<button id="scrollTop" aria-label="Cuộn lên đầu trang">{!! \App\Helpers\Icon::get('up') !!}</button>
<script src="{{ asset('js/main.js') }}"></script>
@stack('js')
</body>
</html>
