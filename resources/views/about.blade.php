@extends('layouts.app')
@section('title','Giới thiệu')
@push('css')<link rel="stylesheet" href="{{ asset('css/shop.css') }}">@endpush

@section('content')
@php $hotline = \App\Models\Setting::get('hotline','0909 123 456'); @endphp
<main class="ab-main">

  {{-- HERO --}}
  <section class="ab-hero">
    <div class="ab-hero-bg"></div>
    <div class="container">
    <nav class="breadcrumb">
      <a href="{{ route('home') }}">Trang chủ</a> <span>›</span>
      <span>Giới thiệu</span>
    </nav>
    <div class="ab-hero-inner container">
      <div class="ab-hero-content">
      <header class="about-page-head">
        <div class="section-eyebrow" style="justify-content:flex-start">Giới thiệu</div>
        <h1 class="ab-hero-title">Đối tác sản xuất<br><span class="ab-accent">mũ bảo hiểm in logo</span><br>tin cậy của doanh nghiệp</h1>
        <p class="ab-hero-desc">Từ 2019, chúng tôi đã đồng hành cùng hơn 500 doanh nghiệp — từ startup đến tập đoàn — trong việc tạo ra những chiếc mũ bảo hiểm mang thương hiệu riêng, đạt chuẩn chất lượng quốc gia.</p>
        <div class="ab-hero-ctas">
          <a href="{{ route('contact') }}" class="btn ab-btn-primary">{!! \App\Helpers\Icon::get('send') !!} Nhận báo giá ngay</a>
          <a href="tel:{{ preg_replace('/\s+/','',$hotline) }}" class="btn ab-btn-ghost">{!! \App\Helpers\Icon::get('phone') !!} {{ $hotline }}</a>
        </div>
        <div class="ab-hero-trust">
          <div class="ab-trust-item">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Đạt chuẩn QCVN 2:2008
          </div>
          <div class="ab-trust-item">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Giao hàng toàn quốc 63 tỉnh thành
          </div>
          <div class="ab-trust-item">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Bảo hành 12 tháng
          </div>
        </div>
      </div>
      <div class="ab-hero-visual">
        <div class="ab-hero-img-wrap">
          <img src="{{ asset('images/banner.png') }}" alt="Xưởng sản xuất mũ bảo hiểm in logo" loading="lazy">
          <div class="ab-hero-img-badge">
            <div class="ab-img-badge-inner">
              <strong>500+</strong>
              <span>Doanh nghiệp<br>tin dùng</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- STATS STRIP --}}
  <div class="ab-stats-strip">
    <div class="container">
      <div class="ab-stats-grid">
        @foreach([
          ['5+','Năm kinh nghiệm','Hoạt động từ 2019'],
          ['500+','Doanh nghiệp','Đã tin tưởng hợp tác'],
          ['50.000+','Chiếc mũ / năm','Công suất sản xuất'],
          ['63','Tỉnh thành','Phủ sóng giao hàng'],
        ] as [$num,$label,$sub])
        <div class="ab-stat">
          <div class="ab-stat-num">{{ $num }}</div>
          <div class="ab-stat-label">{{ $label }}</div>
          <div class="ab-stat-sub">{{ $sub }}</div>
        </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="container">

    {{-- VALUES --}}
    <section class="ab-section">
      <div class="ab-section-header">
        <div class="ab-eyebrow">Giá trị cốt lõi</div>
        <h2 class="ab-section-title">Điều chúng tôi cam kết</h2>
        <p class="ab-section-desc">Bốn nguyên tắc định hình mọi quyết định — từ chọn nguyên liệu đến giao hàng tận nơi.</p>
      </div>
      <div class="ab-values-grid">
        @foreach([
          ['01','shield','Chất lượng không thỏa hiệp','Mỗi lô hàng đều được kiểm tra kỹ trước khi xuất xưởng. Chúng tôi không giao sản phẩm lỗi — dù chỉ một chiếc.'],
          ['02','clock','Đúng hẹn là tôn trọng','Tiến độ giao hàng được cam kết bằng văn bản. Chúng tôi hiểu rằng sự kiện và kế hoạch của doanh nghiệp không thể chờ đợi.'],
          ['03','support','Đồng hành từ đầu đến cuối','Từ tư vấn chọn mẫu, thiết kế logo đến theo dõi đơn hàng — đội ngũ luôn sẵn sàng hỗ trợ trong suốt quá trình.'],
          ['04','spark','Sáng tạo theo thương hiệu','Không có mẫu cố định. Mỗi đơn hàng là một sản phẩm riêng — màu sắc, logo, kiểu dáng đều theo đúng nhận diện thương hiệu của khách.'],
        ] as [$num,$icon,$title,$desc])
        <div class="ab-value-card fade-up">
          <div class="ab-value-num">{{ $num }}</div>
          <div class="ab-value-icon">{!! \App\Helpers\Icon::get($icon) !!}</div>
          <div class="ab-value-body">
            <h3>{{ $title }}</h3>
            <p>{{ $desc }}</p>
          </div>
        </div>
        @endforeach
      </div>
    </section>

    {{-- PROCESS --}}
    <section class="ab-section ab-process-section">
      <div class="ab-section-header">
        <div class="ab-eyebrow">Quy trình làm việc</div>
        <h2 class="ab-section-title">Đặt hàng đơn giản, 4 bước</h2>
        <p class="ab-section-desc">Từ lúc liên hệ đến khi nhận hàng — quy trình minh bạch, không phát sinh chi phí ẩn.</p>
      </div>
      <div class="ab-process-grid">
        @foreach([
          ['01','send','Liên hệ & Tư vấn','Gửi yêu cầu qua form hoặc gọi hotline. Chúng tôi phản hồi trong vòng 2 giờ làm việc.'],
          ['02','spark','Thiết kế & Xác nhận','Đội ngũ thiết kế tạo mockup theo logo và màu sắc thương hiệu. Chỉnh sửa không giới hạn đến khi ưng ý.'],
          ['03','shield','Sản xuất & Kiểm định','Sản xuất theo đúng mẫu đã duyệt. Kiểm tra chất lượng 100% trước khi đóng gói.'],
          ['04','check','Giao hàng toàn quốc','Đóng gói cẩn thận, giao tận nơi qua đơn vị vận chuyển uy tín. Có mã theo dõi đơn hàng.'],
        ] as [$step,$icon,$title,$desc])
        <div class="ab-process-card fade-up">
          <div class="ab-process-step">{{ $step }}</div>
          <div class="ab-process-icon">{!! \App\Helpers\Icon::get($icon) !!}</div>
          <h3>{{ $title }}</h3>
          <p>{{ $desc }}</p>
        </div>
        @endforeach
      </div>
    </section>

    {{-- CERTIFICATIONS --}}
    <section class="ab-section">
      <div class="ab-section-header">
        <div class="ab-eyebrow">Chứng nhận</div>
        <h2 class="ab-section-title">Tiêu chuẩn chất lượng</h2>
        <p class="ab-section-desc">Tất cả sản phẩm đều đáp ứng các tiêu chuẩn bắt buộc theo quy định pháp luật Việt Nam.</p>
      </div>
      <div class="ab-cert-grid">
        @foreach([
          ['shield','QCVN 2:2008','Quy chuẩn kỹ thuật quốc gia','Toàn bộ sản phẩm đạt QCVN 2:2008/BKHCN — tiêu chuẩn bắt buộc cho mũ bảo hiểm lưu thông tại Việt Nam. Có tem hợp quy CR và hồ sơ chứng nhận đầy đủ.'],
          ['check','Kiểm định độc lập','Thử nghiệm bởi bên thứ ba','Sản phẩm được kiểm định tại các tổ chức được Bộ KHCN chỉ định. Kết quả kiểm định có thể cung cấp theo yêu cầu của doanh nghiệp.'],
          ['clock','Bảo hành 12 tháng','Cam kết sau bán hàng','Bảo hành 12 tháng cho lỗi sản xuất. Hỗ trợ đổi trả trong 7 ngày nếu sản phẩm không đúng với mẫu đã xác nhận.'],
        ] as [$icon,$badge,$title,$desc])
        <div class="ab-cert-card fade-up">
          <div class="ab-cert-icon">{!! \App\Helpers\Icon::get($icon) !!}</div>
          <div class="ab-cert-badge">{{ $badge }}</div>
          <h3>{{ $title }}</h3>
          <p>{{ $desc }}</p>
        </div>
        @endforeach
      </div>
    </section>

  </div>

  {{-- CTA --}}
  <div class="container">
    <section class="shop-cta">
      <div class="shop-cta-text">
        <h2>Sẵn sàng đặt hàng mũ bảo hiểm in logo?</h2>
        <p>Liên hệ ngay để được tư vấn miễn phí và nhận báo giá trong 2 giờ làm việc.</p>
      </div>
      <div class="shop-cta-actions">
        <a href="{{ route('contact') }}" class="btn btn-primary">{!! \App\Helpers\Icon::get('send') !!} Nhận báo giá ngay</a>
        <a href="tel:{{ preg_replace('/\s+/','',$hotline) }}" class="btn btn-outline-dark">{!! \App\Helpers\Icon::get('phone') !!} {{ $hotline }}</a>
      </div>
    </section>
  </div>

</main>
@endsection
