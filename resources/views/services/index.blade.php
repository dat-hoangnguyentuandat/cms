@extends('layouts.app')
@section('title','Dịch vụ')
@push('css')<link rel="stylesheet" href="{{ asset('css/shop.css') }}">@endpush

@section('content')
@php $hotline = \App\Models\Setting::get('hotline','0909 123 456'); @endphp
<main class="shop-main">
  <div class="container">
    <nav class="breadcrumb">
      <a href="{{ route('home') }}">Trang chủ</a> <span>›</span>
      <span>Dịch vụ</span>
    </nav>

    <header class="shop-page-head">
      <div class="section-eyebrow" style="justify-content:flex-start">Dịch vụ</div>
      <h1 class="page-title" style="margin-bottom:8px">Các dịch vụ của chúng tôi</h1>

      <form method="GET" action="{{ route('services.index') }}" class="shop-search-form" role="search">
        <input type="search" name="q" value="{{ $keyword ?? '' }}" placeholder="Tìm dịch vụ theo tên..." aria-label="Tìm dịch vụ">
        @if($activeSlug)<input type="hidden" name="cat" value="{{ $activeSlug }}">@endif
        <button type="submit" class="btn btn-primary">{!! \App\Helpers\Icon::get('arrow') !!} Tìm</button>
      </form>
    </header>

    <div class="products-filter" aria-label="Lọc dịch vụ theo danh mục">
      <a href="{{ route('services.index', $keyword ? ['q'=>$keyword] : []) }}"
         class="filter-btn @if(!$activeSlug) active @endif">Tất cả</a>
      @foreach($categories as $cat)
        <a href="{{ route('services.index', array_filter(['cat'=>$cat->slug,'q'=>$keyword])) }}"
           class="filter-btn @if($activeSlug === $cat->slug) active @endif">{{ $cat->name }}</a>
      @endforeach
    </div>

    @if($services->isEmpty())
      <div class="shop-empty">
        <p>Không tìm thấy dịch vụ phù hợp.</p>
        <a href="{{ route('services.index') }}" class="btn btn-outline-dark">Xem tất cả dịch vụ</a>
      </div>
    @else
      <div class="products-grid">
        @foreach($services as $service)
        <article class="product-card">
          <div class="product-img-wrap">
            <a href="{{ route('service.show', $service->slug) }}">
              @if($service->image)
                <img src="{{ $service->image_url }}" alt="{{ $service->name }}" loading="lazy" width="900" height="650">
              @else
                <div class="pd-placeholder" style="height:200px;display:flex;align-items:center;justify-content:center;background:#f1f5f9;color:#94a3b8">Chưa có ảnh</div>
              @endif
            </a>
            @if($service->is_featured)
              <span class="product-badge badge-hot">Nổi bật</span>
            @endif
          </div>
          <div class="product-body">
            @if($service->category)<div class="product-brand">{{ $service->category->name }}</div>@endif
            <h3 class="product-name"><a href="{{ route('service.show', $service->slug) }}">{{ $service->name }}</a></h3>
            <div class="product-summary rich-content">{!! cms_content($service->short_desc, $service) !!}</div>
            <div class="product-price-row">
              @if($service->price > 0)
                <span class="price-now">{{ number_format($service->price,0,',','.').'đ' }}</span>
              @else
                <span class="price-now">Liên hệ</span>
              @endif
              @if($service->duration)
                <span style="font-size:12px;color:#64748b;margin-left:8px">· {{ $service->duration }}</span>
              @endif
            </div>
            <a class="btn-add-cart" href="{{ route('service.show', $service->slug) }}">{!! \App\Helpers\Icon::get('arrow') !!} Xem chi tiết</a>
          </div>
        </article>
        @endforeach
      </div>

      <div class="shop-pagination">
        {{ $services->links() }}
      </div>
    @endif

    <section class="shop-cta">
      <div class="shop-cta-text">
        <h2>Cần tư vấn dịch vụ phù hợp?</h2>
        <p>Liên hệ ngay để được tư vấn miễn phí và nhận báo giá trong 2 giờ làm việc.</p>
      </div>
      <div class="shop-cta-actions">
        <a href="{{ route('contact') }}" class="btn btn-primary">{!! \App\Helpers\Icon::get('send') !!} Nhận báo giá</a>
        <a href="tel:{{ preg_replace('/\s+/','',$hotline) }}" class="btn btn-outline-dark">{!! \App\Helpers\Icon::get('phone') !!} {{ $hotline }}</a>
      </div>
    </section>
  </div>
</main>
@endsection
