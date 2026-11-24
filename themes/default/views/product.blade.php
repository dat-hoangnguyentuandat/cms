@extends('layouts.app')
@section('title', $product->name)
@section('description', strip_tags($product->short_desc))

@section('content')
@php $hotline = \App\Models\Setting::get('hotline','0909 123 456'); @endphp

<div class="page">
  <div class="container">
    <nav class="breadcrumb">
      <a href="{{ route('home') }}">Trang chủ</a> ·
      <a href="{{ route('products.index') }}">Sản phẩm</a> ·
      <span>{{ $product->name }}</span>
    </nav>

    <div class="detail-grid">
      <div class="detail-img">
        @if($product->image)<img src="{{ $product->image_url }}" alt="{{ $product->name }}">@endif
      </div>
      <div class="detail-info">
        @if($product->category)<div class="product-cat">{{ $product->category->name }}</div>@endif
        <h1>{{ $product->name }}</h1>
        <div class="detail-price">
          {{ number_format($product->display_price,0,',','.') }}đ
          @if($product->sale_price)
            <span class="detail-price-old">{{ number_format($product->price,0,',','.') }}đ</span>
          @endif
        </div>
        <div class="detail-short article-body">{!! cms_content($product->short_desc, $product) !!}</div>
        <div class="detail-actions">
          <a href="{{ route('contact') }}" class="btn btn-primary btn-lg">Liên hệ đặt mua</a>
          <a href="tel:{{ preg_replace('/\s+/','',$hotline) }}" class="btn btn-outline btn-lg">{{ $hotline }}</a>
        </div>
      </div>
    </div>

    @if($product->description)
    <div class="detail-body article-body">
      <h2>Chi tiết sản phẩm</h2>
      {!! cms_content($product->description, $product) !!}
    </div>
    @endif

    @if($related->count())
    <div style="margin-top:60px">
      <h2 style="font-size:24px;margin:0 0 24px">Sản phẩm liên quan</h2>
      <div class="product-grid">
        @foreach($related as $r)
        <article class="product-card">
          <a href="{{ route('product.show', $r->slug) }}" class="product-img">
            <img src="{{ $r->image_url }}" alt="{{ $r->name }}" loading="lazy">
          </a>
          <div class="product-body">
            <h3><a href="{{ route('product.show', $r->slug) }}">{{ $r->name }}</a></h3>
            <div class="product-price">{{ number_format($r->display_price,0,',','.') }}đ</div>
          </div>
        </article>
        @endforeach
      </div>
    </div>
    @endif
  </div>
</div>
@endsection
