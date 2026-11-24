@extends('layouts.app')
@section('title','Sản phẩm')

@section('content')
<div class="page">
  <div class="container">
    <nav class="breadcrumb"><a href="{{ route('home') }}">Trang chủ</a> · <span>Sản phẩm</span></nav>
    <h1 class="page-title">Tất cả sản phẩm</h1>
    <p class="page-desc">Khám phá bộ sưu tập sản phẩm đa dạng — chất lượng cao, giá cả hợp lý.</p>

    <form method="GET" action="{{ route('products.index') }}" style="display:flex;gap:10px;margin-bottom:24px;flex-wrap:wrap">
      <input type="search" name="q" value="{{ $keyword ?? '' }}" placeholder="Tìm kiếm sản phẩm..."
             class="form-input" style="flex:1;min-width:240px">
      <button type="submit" class="btn btn-primary">Tìm</button>
    </form>

    @if($categories->isNotEmpty())
    <div class="pill-group">
      <a href="{{ route('products.index', $keyword ? ['q'=>$keyword] : []) }}"
         class="pill {{ !$activeSlug ? 'active' : '' }}">Tất cả</a>
      @foreach($categories as $cat)
        <a href="{{ route('products.index', array_filter(['cat'=>$cat->slug,'q'=>$keyword])) }}"
           class="pill {{ $activeSlug===$cat->slug ? 'active' : '' }}">{{ $cat->name }}</a>
      @endforeach
    </div>
    @endif

    @if($products->isEmpty())
      <div class="empty-state">
        <p>Không có sản phẩm phù hợp.</p>
        <a href="{{ route('products.index') }}" class="btn btn-outline">Xem tất cả sản phẩm</a>
      </div>
    @else
    <div class="product-grid">
      @foreach($products as $product)
      <article class="product-card fade-in">
        <a href="{{ route('product.show', $product->slug) }}" class="product-img">
          <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
        </a>
        <div class="product-body">
          @if($product->category)<div class="product-cat">{{ $product->category->name }}</div>@endif
          <h3><a href="{{ route('product.show', $product->slug) }}">{{ $product->name }}</a></h3>
          <div class="product-price">
            {{ number_format($product->display_price,0,',','.') }}đ
            @if($product->sale_price)
              <span class="product-price-old">{{ number_format($product->price,0,',','.') }}đ</span>
            @endif
          </div>
        </div>
      </article>
      @endforeach
    </div>
    <div class="pagination-wrap">{{ $products->links('pagination::theme') }}</div>
    @endif
  </div>
</div>
@endsection
