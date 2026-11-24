@extends('layouts.app')
@section('title', $service->name)
@section('description', strip_tags($service->short_desc))
@push('css')<link rel="stylesheet" href="{{ asset('css/shop.css') }}">@endpush

@section('content')
@php $hotline = \App\Models\Setting::get('hotline','0909 123 456'); @endphp
<main class="shop-main">
  <div class="container">
    <nav class="breadcrumb">
      <a href="{{ route('home') }}">Trang chủ</a> <span>›</span>
      <a href="{{ route('services.index') }}">Dịch vụ</a> <span>›</span>
      <span>{{ $service->name }}</span>
    </nav>

    <div class="product-detail">
      <div class="pd-gallery">
        @if($service->image)
          <img src="{{ $service->image_url }}" alt="{{ $service->name }}">
        @else
          <div class="pd-placeholder">Chưa có ảnh</div>
        @endif
      </div>
      <div class="pd-info">
        @if($service->category)<div class="pd-cat">{{ $service->category->name }}</div>@endif
        <h1 class="pd-title">{{ $service->name }}</h1>
        <div class="pd-meta">
          @if($service->duration)Thời gian: <b>{{ $service->duration }}</b> ·@endif
          {{ number_format($service->views) }} lượt xem
        </div>
        <div class="pd-price">
          @if($service->price > 0)
            <span class="price-now">{{ number_format($service->price,0,',','.').'đ' }}</span>
          @else
            <span class="price-now">Liên hệ báo giá</span>
          @endif
        </div>
        <div class="pd-short rich-content">{!! cms_content($service->short_desc, $service) !!}</div>
        <div class="pd-cta-group">
          <a href="{{ route('contact') }}" class="btn btn-primary">Đặt lịch / Báo giá</a>
          <a href="tel:{{ preg_replace('/\s+/','',$hotline) }}" class="btn btn-outline-dark">{{ $hotline }}</a>
        </div>
      </div>
    </div>

    @if($service->description)
    <div class="pd-desc-box">
      <h2>Mô tả dịch vụ</h2>
      <div class="pd-desc-content rich-content">{!! cms_content($service->description, $service) !!}</div>
    </div>
    @endif

    @if($related->count())
    <div class="pd-related">
      <h2>Dịch vụ liên quan</h2>
      <div class="products-grid">
        @foreach($related as $r)
        <article class="product-card">
          <div class="product-img-wrap">
            <a href="{{ route('service.show', $r->slug) }}">
              @if($r->image)
                <img src="{{ $r->image_url }}" alt="{{ $r->name }}" loading="lazy">
              @endif
            </a>
          </div>
          <div class="product-body">
            <h3 class="product-name"><a href="{{ route('service.show', $r->slug) }}">{{ $r->name }}</a></h3>
            <div class="product-price-row">
              <span class="price-now">{{ $r->price > 0 ? number_format($r->price,0,',','.').'đ' : 'Liên hệ' }}</span>
            </div>
          </div>
        </article>
        @endforeach
      </div>
    </div>
    @endif
  </div>
</main>
@endsection
