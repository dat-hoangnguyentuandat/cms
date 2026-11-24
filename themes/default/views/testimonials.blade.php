@extends('layouts.app')
@section('title', 'Đánh giá khách hàng')

@section('content')
<section class="page">
  <div class="container">
    <nav class="breadcrumb"><a href="{{ route('home') }}">Trang chủ</a> / Đánh giá</nav>
    <h1 class="page-title">Khách hàng nói gì về chúng tôi</h1>
    <p class="page-desc">Trải nghiệm thực tế từ những khách hàng đã đồng hành cùng chúng tôi.</p>

    <div class="testimonial-grid">
      @forelse($testimonials as $t)
        <div class="testimonial">
          <div class="stars">{{ str_repeat('★', (int)($t->rating ?: 5)) }}</div>
          <p class="testimonial-quote">"{{ $t->content }}"</p>
          <div class="testimonial-author">
            <div class="testimonial-avatar">{{ mb_strtoupper(mb_substr($t->reviewer_name ?: $t->name, 0, 1, 'UTF-8'), 'UTF-8') }}</div>
            <div>
              <div class="testimonial-name">{{ $t->reviewer_name ?: $t->name }}</div>
              @if($t->role)<div class="testimonial-role">{{ $t->role }}</div>@endif
            </div>
          </div>
        </div>
      @empty
        <div class="empty-state">Chưa có đánh giá nào.</div>
      @endforelse
    </div>

    @if($testimonials->hasPages())
      <div class="pagination-wrap">{{ $testimonials->links('pagination::theme') }}</div>
    @endif

    @if(!empty($googleReviewUrl))
      <div style="text-align:center;margin-top:36px">
        <a class="btn btn-primary" href="{{ $googleReviewUrl }}" target="_blank" rel="noopener">Viết đánh giá trên Google</a>
      </div>
    @endif
  </div>
</section>
@endsection
