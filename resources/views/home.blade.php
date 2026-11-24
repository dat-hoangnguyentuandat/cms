@extends('layouts.app')

@section('title', 'Mũ Bảo Hiểm In Logo Chuyên Nghiệp')

@section('content')
@php
  $about_title   = \App\Models\Setting::get('about_title','Đơn vị sản xuất mũ bảo hiểm in logo hàng đầu');
  $about_desc    = \App\Models\Setting::get('about_desc','Chúng tôi chuyên sản xuất mũ bảo hiểm in logo theo yêu cầu doanh nghiệp.');
  $hotline       = \App\Models\Setting::get('hotline','0909 123 456');
  $address       = \App\Models\Setting::get('address','TP. Hồ Chí Minh');
  $email         = \App\Models\Setting::get('email','contact@mubaohiemquatang.com');
  $working_hours = \App\Models\Setting::get('working_hours','08:00 - 18:00 từ thứ 2 đến thứ 7');
  $features = [
    ['icon'=>'shield','title'=>'In logo sắc nét, bền màu','desc'=>'Công nghệ in lụa và in nhiệt đảm bảo logo rõ ràng, bền màu qua nhiều lần sử dụng và giặt rửa.'],
    ['icon'=>'spark', 'title'=>'Số lượng linh hoạt',      'desc'=>'Nhận đơn từ 50 chiếc trở lên. Giá ưu đãi theo số lượng, phù hợp mọi quy mô doanh nghiệp.'],
    ['icon'=>'truck', 'title'=>'Đạt chuẩn an toàn',       'desc'=>'Tất cả sản phẩm đạt tiêu chuẩn QCVN 2:2008/BKHCN, có tem kiểm định và hồ sơ chứng nhận.'],
    ['icon'=>'support','title'=>'Tư vấn & hỗ trợ tận tâm','desc'=>'Hỗ trợ thiết kế logo miễn phí, tư vấn chọn màu sắc và kiểu dáng phù hợp với nhận diện thương hiệu.'],
  ];
  $process = [
    ['title'=>'Tư vấn & chọn mẫu',           'desc'=>'Khách hàng cho biết số lượng, ngân sách và phong cách thương hiệu. Đội ngũ tư vấn dòng mũ phù hợp.'],
    ['title'=>'Thiết kế logo & mẫu in',       'desc'=>'Nhận file logo từ khách, thiết kế bản in thử trên mũ và gửi duyệt trước khi sản xuất.'],
    ['title'=>'Sản xuất & kiểm tra chất lượng','desc'=>'Sản xuất theo số lượng đã xác nhận, kiểm tra từng lô hàng đảm bảo logo sắc nét, mũ đạt chuẩn.'],
    ['title'=>'Đóng gói & giao hàng',         'desc'=>'Đóng gói theo yêu cầu (hộp quà, túi vải...), giao hàng toàn quốc đúng tiến độ cam kết.'],
  ];
@endphp

<section id="hero" aria-label="Hero banner">
  <div class="hero-wrap">
    <img src="{{ asset('images/banner.png') }}" alt="Mũ bảo hiểm in logo chuyên nghiệp cho doanh nghiệp" class="hero-banner-img">
  </div>
</section>

<section id="stats" aria-label="Thành tích">
  <div class="container"><div class="stats-inner">
    <div class="stat-item"><div class="stat-number" data-count="500" data-suffix="+">0+</div><div class="stat-label">Khách hàng</div></div>
    <div class="stat-item"><div class="stat-number" data-count="1000" data-suffix="+">0+</div><div class="stat-label">Đơn hàng</div></div>
    <div class="stat-item"><div class="stat-number" data-count="5" data-suffix=" năm">0 năm</div><div class="stat-label">Kinh nghiệm</div></div>
  </div></div>
</section>

<section id="about" aria-labelledby="about-title">
  <div class="container split-section">
    <div class="section-copy fade-up">
      <div class="section-eyebrow">Giới thiệu</div>
      <h2 class="section-title" id="about-title">{{ $about_title }}</h2>
      <div class="section-desc rich-content">{!! $about_desc !!}</div>
      <div class="bullet-list">
        <span>{!! \App\Helpers\Icon::get('check') !!} Sản xuất theo yêu cầu — in logo, slogan, màu sắc riêng</span>
        <span>{!! \App\Helpers\Icon::get('check') !!} Đạt chuẩn QCVN 2:2008/BKHCN, có tem kiểm định đầy đủ</span>
        <span>{!! \App\Helpers\Icon::get('check') !!} Giao hàng toàn quốc, đúng tiến độ cam kết</span>
      </div>
      <a href="{{ route('about') }}" class="btn btn-outline-dark" style="margin-top:24px;display:inline-flex;align-items:center;gap:6px">{!! \App\Helpers\Icon::get('arrow') !!} Xem thêm về chúng tôi</a>
    </div>
    <div class="visual-card fade-up">
      <img src="{{ asset('images/banner.png') }}" alt="Mũ bảo hiểm in logo doanh nghiệp" loading="lazy">
      <div class="visual-note"><strong>500+ doanh nghiệp tin dùng</strong><span>Từ startup đến tập đoàn lớn trên toàn quốc.</span></div>
    </div>
  </div>
</section>

<section id="products" aria-labelledby="products-title">
  <div class="container">
    <div class="section-header">
      <div class="section-eyebrow">Sản phẩm</div>
      <h2 class="section-title" id="products-title">Các dòng mũ bảo hiểm in logo</h2>
      <p class="section-desc">Đa dạng kiểu dáng — nửa đầu, 3/4, fullface, thể thao. Tất cả đều có thể in logo theo yêu cầu doanh nghiệp.</p>
    </div>
    <div class="products-filter" aria-label="Lọc sản phẩm">
      <button class="filter-btn active" data-filter="all" type="button">Tất cả</button>
      @foreach($categories as $cat)
        <button class="filter-btn" data-filter="{{ $cat->slug }}" type="button">{{ $cat->name }}</button>
      @endforeach
    </div>
    <div class="products-grid">
      @foreach($products as $product)
      <article class="product-card fade-up" data-category="{{ $product->category?->slug ?? '' }}">
        <div class="product-img-wrap">
          <a href="{{ route('product.show', $product->slug) }}">
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" width="900" height="650">
          </a>
          @if($product->sale_price)
            <span class="product-badge badge-hot">-{{ round((1 - $product->sale_price / max($product->price,1)) * 100) }}%</span>
          @elseif($product->is_featured)
            <span class="product-badge badge-hot">Nổi bật</span>
          @endif
        </div>
        <div class="product-body">
          <div class="product-brand">{{ $product->brand ?: 'Nón bảo hiểm' }}</div>
          <h3 class="product-name"><a href="{{ route('product.show', $product->slug) }}">{{ $product->name }}</a></h3>
          <div class="product-summary rich-content">{!! cms_content($product->short_desc, $product) !!}</div>
          <div class="product-price-row">
            @if($product->sale_price)
              <span class="price-now">{{ number_format($product->sale_price,0,',','.').'đ' }}</span>
              <span class="price-old" style="text-decoration:line-through;color:#94a3b8;margin-left:8px;font-size:13px">{{ number_format($product->price,0,',','.').'đ' }}</span>
            @else
              <span class="price-now">{{ number_format($product->price,0,',','.').'đ' }}</span>
            @endif
          </div>
          <a class="btn-add-cart" href="{{ route('product.show', $product->slug) }}">{!! \App\Helpers\Icon::get('arrow') !!} Xem chi tiết</a>
        </div>
      </article>
      @endforeach
    </div>
  </div>
</section>

<section id="service" aria-labelledby="service-title">
  <div class="container">
    <div class="section-header">
      <div class="section-eyebrow">Dịch vụ</div>
      <h2 class="section-title" id="service-title">Tại sao chọn mũ bảo hiểm in logo của chúng tôi?</h2>
      <p class="section-desc">Cam kết chất lượng từ khâu thiết kế đến thành phẩm — đúng hạn, đúng mẫu, đúng chuẩn.</p>
    </div>
    <div class="features-grid">
      @foreach($features as $feature)
      <div class="feature-card fade-up">
        <div class="feature-icon" aria-hidden="true">{!! \App\Helpers\Icon::get($feature['icon']) !!}</div>
        <h3 class="feature-title">{{ $feature['title'] }}</h3>
        <p class="feature-desc">{{ $feature['desc'] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

<section id="process" aria-labelledby="process-title">
  <div class="container">
    <div class="section-header">
      <div class="section-eyebrow">Quy trình</div>
      <h2 class="section-title" id="process-title">Quy trình đặt hàng mũ bảo hiểm in logo</h2>
      <p class="section-desc">Từ tư vấn đến giao hàng — đơn giản, minh bạch và đúng tiến độ cam kết.</p>
    </div>
    <div class="process-grid">
      @foreach($process as $i => $step)
      <div class="process-card fade-up">
        <span class="process-number">0{{ $i + 1 }}</span>
        <h3>{{ $step['title'] }}</h3>
        <p>{{ $step['desc'] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

<section id="testimonials" aria-labelledby="testimonials-title">
  <div class="container">
    <div class="section-header">
      <div class="section-eyebrow">Đánh giá khách hàng</div>
      <h2 class="section-title" id="testimonials-title">Doanh nghiệp nói gì về chúng tôi?</h2>
    </div>
    <div class="testimonials-grid">
      @foreach($testimonials as $t)
      <blockquote class="testimonial-card fade-up">
        <div class="t-stars">{{ str_repeat('★',$t->rating) . str_repeat('☆',5-$t->rating) }}</div>
        <div class="t-text rich-content">{!! cms_content($t->content, $t) !!}</div>
        <footer class="t-author">
          <div class="avatar-letter">{{ mb_substr($t->name,0,1,'UTF-8') }}</div>
          <div><div class="t-name">{{ $t->name }}</div><div class="t-location">{{ $t->role }}</div></div>
        </footer>
      </blockquote>
      @endforeach
    </div>
  </div>
</section>

<section id="news" aria-labelledby="news-title">
  <div class="container">
    <div class="section-header">
      <div class="section-eyebrow">Tin tức</div>
      <h2 class="section-title" id="news-title">Kiến thức & kinh nghiệm về mũ bảo hiểm doanh nghiệp</h2>
    </div>
    <div class="project-grid">
      @foreach($news as $n)
      <article class="project-card fade-up">
        @if($n->image)<img src="{{ $n->image_url }}" alt="{{ $n->title }}" loading="lazy">@endif
        <div>
          <h3><a href="{{ route('news.show', $n->slug) }}">{{ $n->title }}</a></h3>
          <p>{{ $n->excerpt }}</p>
          <a href="{{ route('news.show', $n->slug) }}" class="news-read-more">Đọc thêm →</a>
        </div>
      </article>
      @endforeach
    </div>
  </div>
</section>

<section id="faq" aria-labelledby="faq-title">
  <div class="container">
    <div class="section-header"><div class="section-eyebrow">FAQ</div><h2 class="section-title" id="faq-title">Câu hỏi thường gặp</h2></div>
    <div class="faq-grid">
      @foreach($faqs as $faq)
      <details class="faq-item fade-up"><summary>{{ $faq->question }}</summary><div class="rich-content">{!! $faq->answer !!}</div></details>
      @endforeach
    </div>
  </div>
</section>

<section id="contact" aria-labelledby="contact-title">
  <div class="container">
    <div class="section-header">
      <div class="section-eyebrow">Liên hệ</div>
      <h2 class="section-title" id="contact-title">Nhận báo giá mũ bảo hiểm in logo</h2>
      <p class="section-desc">Gửi yêu cầu để được tư vấn số lượng, kiểu dáng, thiết kế logo và báo giá nhanh trong 2 giờ làm việc.</p>
    </div>
    <div class="contact-grid">
      <div>
        <h3 class="contact-info-title">Thông tin liên hệ</h3>
        <p class="contact-info-desc">Liên hệ trực tiếp qua hotline/Zalo để được tư vấn nhanh nhất về đơn hàng in logo doanh nghiệp.</p>
        <div class="contact-items">
          <div class="contact-item"><div class="contact-item-icon">{!! \App\Helpers\Icon::get('location') !!}</div><div><div class="contact-item-label">Địa chỉ</div><div class="contact-item-value">{{ $address }}</div></div></div>
          <div class="contact-item"><div class="contact-item-icon">{!! \App\Helpers\Icon::get('phone') !!}</div><div><div class="contact-item-label">Hotline/Zalo</div><div class="contact-item-value"><a href="tel:{{ preg_replace('/\s+/','',$hotline) }}">{{ $hotline }}</a></div></div></div>
          <div class="contact-item"><div class="contact-item-icon">{!! \App\Helpers\Icon::get('mail') !!}</div><div><div class="contact-item-label">Email</div><div class="contact-item-value"><a href="mailto:{{ $email }}">{{ $email }}</a></div></div></div>
          <div class="contact-item"><div class="contact-item-icon">{!! \App\Helpers\Icon::get('clock') !!}</div><div><div class="contact-item-label">Giờ làm việc</div><div class="contact-item-value">{{ $working_hours }}</div></div></div>
        </div>
        <iframe class="map-embed" title="Google Maps TP.HCM" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps?q=Ho%20Chi%20Minh%20City&output=embed"></iframe>
      </div>
      <div class="contact-form-box">
        <form id="contactForm" novalidate>
          @csrf
          <div class="form-row">
            <div class="form-group"><label for="name">Họ và tên</label><input type="text" id="name" name="name" placeholder="Nguyễn Văn A" autocomplete="name" required><span class="error-msg" role="alert"></span></div>
            <div class="form-group"><label for="phone">Số điện thoại</label><input type="tel" id="phone" name="phone" placeholder="0909123456" autocomplete="tel" required><span class="error-msg" role="alert"></span></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email" placeholder="email@congty.com" autocomplete="email"><span class="error-msg" role="alert"></span></div>
            <div class="form-group"><label for="subject">Nhu cầu</label>
              <select id="subject" name="subject">
                <option>Báo giá mũ in logo</option><option>Tư vấn kiểu dáng</option>
                <option>Đặt hàng số lượng lớn</option><option>Thiết kế logo</option><option>Khác</option>
              </select>
            </div>
          </div>
          <div class="form-group"><label for="message">Mô tả yêu cầu</label><textarea id="message" name="message" placeholder="Ví dụ: Cần 200 mũ nửa đầu in logo công ty, màu đen, giao trong 2 tuần..." required></textarea><span class="error-msg" role="alert"></span></div>
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
</section>
@endsection

@push('js')
<script>
// Override contact form to post to Laravel route
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
