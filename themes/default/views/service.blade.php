@extends('layouts.app')
@section('title', $service->name)
@section('description', strip_tags(str_replace("\n", ' ', (string) $service->short_desc)))

@section('content')
@php $hotline = \App\Models\Setting::get('hotline','0909 123 456'); @endphp

<div class="page">
  <div class="container">
    <nav class="breadcrumb">
      <a href="{{ route('home') }}">Trang chủ</a> ·
      <a href="{{ route('services.index') }}">Dịch vụ</a> ·
      <span>{{ $service->name }}</span>
    </nav>

    <div class="detail-grid">
      <div class="detail-img">
        @if($service->image)<img src="{{ $service->image_url }}" alt="{{ $service->name }}">@endif
      </div>
      <div class="detail-info">
        @if($service->category)<div class="product-cat">{{ $service->category->name }}</div>@endif
        <h1>{{ $service->name }}</h1>
        <div class="detail-price">
          @if($service->price > 0)
            Từ {{ number_format($service->price,0,',','.') }}đ
          @else
            <span style="color:var(--primary)">Liên hệ báo giá</span>
          @endif
        </div>
        <div class="detail-short article-body" style="white-space:pre-line">{{ strip_tags((string) $service->short_desc) }}</div>
        <div class="detail-actions">
          <a href="{{ route('contact') }}" class="btn btn-primary btn-lg">Liên hệ tư vấn</a>
          <a href="tel:{{ preg_replace('/\s+/','',$hotline) }}" class="btn btn-outline btn-lg">{{ $hotline }}</a>
        </div>
      </div>
    </div>

    @if($service->description)
    <div class="detail-body article-body">
      <h2>Chi tiết dịch vụ</h2>
      {!! cms_content($service->description, $service) !!}
    </div>
    @endif

    @if($related->count())
    <div style="margin-top:60px">
      <h2 style="font-size:24px;margin:0 0 24px">Dịch vụ liên quan</h2>
      <div class="product-grid">
        @foreach($related as $r)
        <article class="product-card">
          <a href="{{ route('service.show', $r->slug) }}" class="product-img">
            @if($r->image)<img src="{{ $r->image_url }}" alt="{{ $r->name }}" loading="lazy">@endif
          </a>
          <div class="product-body">
            <h3><a href="{{ route('service.show', $r->slug) }}">{{ $r->name }}</a></h3>
            <p class="product-desc">{{ \Illuminate\Support\Str::limit(strip_tags(str_replace("\n", ' ', (string) $r->short_desc)), 80) }}</p>
          </div>
        </article>
        @endforeach
      </div>
    </div>
    @endif
  </div>
</div>
@endsection
