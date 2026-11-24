@extends('layouts.app')
@section('title','Giới thiệu')

@section('content')
@php
  $about_title = \App\Models\Setting::get('about_title','Câu chuyện của chúng tôi');
  $about_desc  = \App\Models\Setting::get('about_desc','<p>Chúng tôi được thành lập với sứ mệnh mang đến sản phẩm và dịch vụ chất lượng cao cho khách hàng.</p>');
@endphp

<div class="page">
  <div class="container">
    <nav class="breadcrumb"><a href="{{ route('home') }}">Trang chủ</a> · <span>Giới thiệu</span></nav>
    <h1 class="page-title">{{ $about_title }}</h1>
    <p class="page-desc">Hành trình xây dựng thương hiệu, giá trị cốt lõi và đội ngũ làm nên thành công hôm nay.</p>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:18px;margin:30px 0 60px">
      @foreach([['10+','Năm kinh nghiệm'],['500+','Khách hàng'],['1.000+','Đơn hàng'],['4.9★','Đánh giá']] as [$n,$l])
      <div class="stat fade-in"><div class="stat-num">{{ $n }}</div><div class="stat-label">{{ $l }}</div></div>
      @endforeach
    </div>

    <div class="article-body">
      {!! $about_desc !!}
    </div>

    <div class="features" style="margin-top:50px">
      @foreach([
        ['Tầm nhìn','Trở thành thương hiệu được khách hàng tin tưởng nhất trong ngành — không chỉ về chất lượng sản phẩm mà còn ở dịch vụ chăm sóc khách hàng.'],
        ['Sứ mệnh','Cung cấp những giải pháp tốt nhất, giúp khách hàng đạt được mục tiêu của mình một cách hiệu quả và bền vững.'],
        ['Giá trị','Trung thực, chất lượng, đổi mới và tận tâm — bốn giá trị làm nên triết lý kinh doanh và mọi quyết định của chúng tôi.'],
      ] as [$t,$d])
      <div class="feature-card fade-in">
        <h3>{{ $t }}</h3>
        <p>{{ $d }}</p>
      </div>
      @endforeach
    </div>

    <div style="margin-top:60px">
      <div class="cta-banner fade-in">
        <h2>Sẵn sàng hợp tác cùng chúng tôi?</h2>
        <p>Liên hệ ngay để bắt đầu — đội ngũ tư vấn sẽ phản hồi trong vòng 2 giờ làm việc.</p>
        <a href="{{ route('contact') }}" class="btn btn-primary btn-lg">Liên hệ ngay</a>
      </div>
    </div>
  </div>
</div>
@endsection
