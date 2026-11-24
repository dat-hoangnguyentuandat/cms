@extends('layouts.app')
@section('title', $product->name)
@section('description', strip_tags($product->short_desc))
@push('css')<link rel="stylesheet" href="{{ asset('css/shop.css') }}">@endpush

@section('content')
@php $hotline = \App\Models\Setting::get('hotline','0909 123 456'); @endphp
<main class="shop-main">
  <div class="container">
    <nav class="breadcrumb">
      <a href="{{ route('home') }}">Trang chủ</a> <span>›</span>
      <a href="{{ route('products.index') }}">Sản phẩm</a> <span>›</span>
      <span>{{ $product->name }}</span>
    </nav>

    <div class="product-detail">
      <div class="pd-gallery">
        @if($product->image)
          <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
        @else
          <div class="pd-placeholder">Chưa có ảnh</div>
        @endif
      </div>
      <div class="pd-info">
        @if($product->category)<div class="pd-cat">{{ $product->category->name }}</div>@endif
        <h1 class="pd-title">{{ $product->name }}</h1>
        <div class="pd-meta">
          @if($product->sku)SKU: <b>{{ $product->sku }}</b>@endif
          @if($product->brand) · Thương hiệu: <b>{{ $product->brand }}</b>@endif
          · {{ number_format($product->views) }} lượt xem
        </div>
        <div class="pd-price">
          <span class="price-now">{{ number_format($product->display_price,0,',','.').'đ' }}</span>
          @if($product->sale_price)
            <span class="price-old">{{ number_format($product->price,0,',','.').'đ' }}</span>
            <span class="price-discount">-{{ round((1-$product->sale_price/max($product->price,1))*100) }}%</span>
          @endif
        </div>
        <div class="pd-short rich-content">{!! cms_content($product->short_desc, $product) !!}</div>
        <div class="pd-stock">
          Tình trạng:
          @if($product->stock > 0)
            <span class="stock-in">Còn hàng ({{ $product->stock }})</span>
          @else
            <span class="stock-out">Tạm hết hàng</span>
          @endif
        </div>
        <div class="pd-trust">
          <div>Hỗ trợ thiết kế logo miễn phí</div>
          <div>Giao hàng toàn quốc, đúng tiến độ</div>
          <div>Đạt chuẩn QCVN 2:2008/BKHCN</div>
        </div>
        <div class="pd-cta-group">
          <a href="{{ route('contact') }}" class="btn btn-primary">Nhận báo giá ngay</a>
          <a href="tel:{{ preg_replace('/\s+/','',$hotline) }}" class="btn btn-outline-dark">{{ $hotline }}</a>
        </div>
      </div>
    </div>

    @if($product->description)
    <div class="pd-desc-box">
      <h2>Mô tả sản phẩm</h2>
      <div class="pd-desc-content rich-content">{!! cms_content($product->description, $product) !!}</div>
    </div>
    @endif

    @if($related->count())
    <div class="pd-related">
      <h2>Sản phẩm liên quan</h2>
      <div class="products-grid">
        @foreach($related as $r)
        <article class="product-card">
          <div class="product-img-wrap">
            <a href="{{ route('product.show', $r->slug) }}"><img src="{{ $r->image_url }}" alt="{{ $r->name }}" loading="lazy"></a>
          </div>
          <div class="product-body">
            <h3 class="product-name"><a href="{{ route('product.show', $r->slug) }}">{{ $r->name }}</a></h3>
            <div class="product-price-row"><span class="price-now">{{ number_format($r->display_price,0,',','.').'đ' }}</span></div>
          </div>
        </article>
        @endforeach
      </div>
    </div>
    @endif
  </div>
</main>
@endsection
