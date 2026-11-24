@php
  // Settings-driven (HomepageBlock casts 'settings' to array; 'data' does not exist)
  $s = $block->settings ?? [];
  $aboutHeading = $s['heading'] ?? 'Về Chúng Tôi';
  $aboutSubheading = $s['subheading'] ?? ($block->title ?: 'Nơi Giao Hòa Của Sự Sang Trọng Và Thư Giãn');
  $aboutDescription = $s['description'] ?? 'Chúng tôi mang đến trải nghiệm đẳng cấp với đội ngũ chuyên gia hàng đầu, thiết bị hiện đại và không gian sang trọng. Cam kết mang lại sự hài lòng tuyệt đối cho mỗi khách hàng.';
  $aboutImageRaw = $s['image'] ?? null;
  $aboutImage = $aboutImageRaw
    ? (\Illuminate\Support\Str::startsWith($aboutImageRaw, ['http://','https://','theme-assets/']) ? asset($aboutImageRaw) : asset('storage/'.$aboutImageRaw))
    : asset('storage/default-spa-about.jpg');
  $ctaText = $s['cta_text'] ?? 'Đặt Lịch Ngay';
  $ctaLink = $s['cta_link'] ?? route('contact');

  // Stats data
  $stats = $s['stats'] ?? [
    ['value' => '10+', 'label' => 'Năm Kinh Nghiệm'],
    ['value' => '20000+', 'label' => 'Khách Hàng Hài Lòng'],
    ['value' => '100%', 'label' => 'Chất Lượng Dịch Vụ'],
    ['value' => '50+', 'label' => 'Dịch Vụ Cao Cấp'],
  ];
@endphp

<section class="spa-about-section">
  <div class="container">
    <div class="about-wrapper">
      <div class="about-content">
        <div class="about-header">
          <span class="about-label">{{ $aboutHeading }}</span>
          <h2 class="about-title">{{ $aboutSubheading }}</h2>
          <div class="about-divider"></div>
        </div>

        <div class="about-description">
          <p>{{ $aboutDescription }}</p>
        </div>

        <div class="stats-badges">
          @foreach($stats as $stat)
            <div class="stat-badge">
              <span class="stat-value">{{ $stat['value'] ?? '' }}</span>
              <span class="stat-label">{{ $stat['label'] ?? '' }}</span>
            </div>
          @endforeach
        </div>

        <div class="about-cta">
          <a href="{{ $ctaLink }}" class="btn-spa-primary">{{ $ctaText }}</a>
        </div>
      </div>

      <div class="about-image-wrapper">
        <div class="about-image">
          <img src="{{ $aboutImage }}" alt="{{ $aboutSubheading }}" loading="lazy">
          <div class="image-decoration"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
/* Spa Luxury About Section */
.spa-about-section {
  background: linear-gradient(135deg, #F5F1E8 0%, #FAF7F0 100%);
  padding: 5rem 0;
  position: relative;
  overflow: hidden;
}

.spa-about-section::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -10%;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(156, 163, 127, 0.08) 0%, transparent 70%);
  border-radius: 50%;
  pointer-events: none;
}

.about-wrapper {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4rem;
  align-items: center;
  position: relative;
  z-index: 1;
}

/* Content Side */
.about-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.about-header {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.about-label {
  font-family: 'Raleway', sans-serif;
  font-size: 0.875rem;
  font-weight: 600;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #9CA37F;
}

.about-title {
  font-family: 'Playfair Display', serif;
  font-size: 2.5rem;
  font-weight: 700;
  line-height: 1.2;
  color: #2C2C2C;
  margin: 0;
}

.about-divider {
  width: 60px;
  height: 3px;
  background: linear-gradient(90deg, #9CA37F 0%, #B8BFA0 100%);
  border-radius: 2px;
}

.about-description {
  font-family: 'Raleway', sans-serif;
  font-size: 1rem;
  line-height: 1.8;
  color: #5A5A5A;
}

.about-description p {
  margin: 0;
}

/* Stats Badges */
.stats-badges {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.25rem;
  margin-top: 1rem;
}

.stat-badge {
  background: #FFFFFF;
  padding: 1.5rem 1.25rem;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 0.5rem;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-badge:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 20px rgba(156, 163, 127, 0.15);
}

.stat-value {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  font-weight: 700;
  color: #9CA37F;
  line-height: 1;
}

.stat-label {
  font-family: 'Raleway', sans-serif;
  font-size: 0.875rem;
  font-weight: 500;
  color: #5A5A5A;
  line-height: 1.3;
}

/* CTA Button */
.about-cta {
  margin-top: 1rem;
}

.btn-spa-primary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 1rem 2.5rem;
  background: linear-gradient(135deg, #9CA37F 0%, #B8BFA0 100%);
  color: #FFFFFF;
  font-family: 'Raleway', sans-serif;
  font-size: 1rem;
  font-weight: 600;
  text-decoration: none;
  border-radius: 50px;
  box-shadow: 0 4px 15px rgba(156, 163, 127, 0.3);
  transition: all 0.3s ease;
  border: none;
  cursor: pointer;
}

.btn-spa-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(156, 163, 127, 0.4);
  background: linear-gradient(135deg, #8B9370 0%, #A8AF91 100%);
}

/* Image Side */
.about-image-wrapper {
  position: relative;
}

.about-image {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.about-image img {
  width: 100%;
  height: auto;
  display: block;
  object-fit: cover;
  aspect-ratio: 4/5;
}

.image-decoration {
  position: absolute;
  top: -20px;
  right: -20px;
  width: 150px;
  height: 150px;
  border: 3px solid #9CA37F;
  border-radius: 12px;
  opacity: 0.3;
  pointer-events: none;
  z-index: -1;
}

/* Responsive Design */
@media (max-width: 992px) {
  .about-wrapper {
    grid-template-columns: 1fr;
    gap: 3rem;
  }

  .about-image-wrapper {
    order: -1;
  }

  .about-title {
    font-size: 2rem;
  }

  .stats-badges {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 576px) {
  .spa-about-section {
    padding: 3rem 0;
  }

  .about-title {
    font-size: 1.75rem;
  }

  .stats-badges {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .stat-value {
    font-size: 1.75rem;
  }

  .btn-spa-primary {
    width: 100%;
    padding: 0.875rem 2rem;
  }

  .image-decoration {
    width: 100px;
    height: 100px;
    top: -15px;
    right: -15px;
  }
}
</style>
