@extends('layouts.app')
@section('title','Sản phẩm mũ bảo hiểm in logo')
@section('description','Danh sách các dòng mũ bảo hiểm in logo theo yêu cầu doanh nghiệp — đa dạng kiểu dáng, đạt chuẩn QCVN 2:2008/BKHCN.')
@push('css')<link rel="stylesheet" href="{{ asset('css/shop.css') }}">@endpush

@section('content')
@php $hotline = \App\Models\Setting::get('hotline','0909 123 456'); @endphp
<main class="shop-main">
  <div class="container">
    <nav class="breadcrumb">
      <a href="{{ route('home') }}">Trang chủ</a> <span>›</span>
      <span>Sản phẩm</span>
    </nav>

    <header class="shop-page-head">
      <div class="section-eyebrow" style="justify-content:flex-start">Sản phẩm</div>
      <h1 class="page-title" style="margin-bottom:8px">Các dòng mũ bảo hiểm in logo</h1>
      <p class="shop-page-desc">Đa dạng kiểu dáng — nửa đầu, 3/4, fullface, thể thao. Tất cả đều có thể in logo theo yêu cầu doanh nghiệp.</p>

      <form method="GET" action="{{ route('products.index') }}" class="shop-search-form" role="search">
        <input type="search" name="q" value="{{ $keyword ?? '' }}" placeholder="Tìm sản phẩm theo tên..." aria-label="Tìm sản phẩm">
        @if($activeSlug)<input type="hidden" name="cat" value="{{ $activeSlug }}">@endif
        <button type="submit" class="btn btn-primary">{!! \App\Helpers\Icon::get('arrow') !!} Tìm</button>
      </form>
    </header>

    <div class="products-filter" aria-label="Lọc sản phẩm theo danh mục">
      <a href="{{ route('products.index', $keyword ? ['q'=>$keyword] : []) }}"
         class="filter-btn @if(!$activeSlug) active @endif">Tất cả</a>
      @foreach($categories as $cat)
        <a href="{{ route('products.index', array_filter(['cat'=>$cat->slug,'q'=>$keyword])) }}"
           class="filter-btn @if($activeSlug === $cat->slug) active @endif">{{ $cat->name }}</a>
      @endforeach
    </div>

    @if($products->isEmpty())
      <div class="shop-empty">
        <p>Không tìm thấy sản phẩm phù hợp.</p>
        <a href="{{ route('products.index') }}" class="btn btn-outline-dark">Xem tất cả sản phẩm</a>
      </div>
    @else
      <div class="products-grid">
        @foreach($products as $product)
        <article class="product-card">
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

      <div class="shop-pagination">
        {{ $products->links() }}
      </div>
    @endif

    <section class="shop-cta">
      <div class="shop-cta-text">
        <h2>Cần tư vấn báo giá theo số lượng?</h2>
        <p>Gửi yêu cầu hoặc gọi hotline để nhận báo giá nhanh trong 2 giờ làm việc.</p>
      </div>
      <div class="shop-cta-actions">
        <a href="{{ route('contact') }}" class="btn btn-primary">{!! \App\Helpers\Icon::get('send') !!} Nhận báo giá</a>
        <a href="tel:{{ preg_replace('/\s+/','',$hotline) }}" class="btn btn-outline-dark">{!! \App\Helpers\Icon::get('phone') !!} {{ $hotline }}</a>
      </div>
    </section>
  </div>
</main>
@endsection
