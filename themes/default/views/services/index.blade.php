@extends('layouts.app')
@section('title', $activeSlug ? (optional($categories->firstWhere('slug', $activeSlug))->name ?? 'Dịch vụ') : 'Dịch vụ')

@section('content')
<div class="page">
  <div class="container">
    <nav class="breadcrumb"><a href="{{ route('home') }}">Trang chủ</a> · <span>Dịch vụ</span></nav>
    <h1 class="page-title">Dịch vụ của chúng tôi</h1>
    <p class="page-desc">Các dịch vụ chất lượng cao, được khách hàng tin tưởng và đánh giá cao.</p>

    <form method="GET" action="{{ route('services.index') }}" style="display:flex;gap:10px;margin-bottom:24px;flex-wrap:wrap">
      <input type="search" name="q" value="{{ $keyword ?? '' }}" placeholder="Tìm dịch vụ..."
             class="form-input" style="flex:1;min-width:240px">
      @if($activeSlug)<input type="hidden" name="cat" value="{{ $activeSlug }}">@endif
      <button type="submit" class="btn btn-primary">Tìm</button>
    </form>

    @if($categories->isNotEmpty())
    <div class="pill-group">
      <a href="{{ route('services.index', $keyword ? ['q'=>$keyword] : []) }}"
         class="pill {{ !$activeSlug ? 'active' : '' }}">Tất cả</a>
      @foreach($categories as $cat)
        <a href="{{ route('services.index', array_filter(['cat'=>$cat->slug,'q'=>$keyword])) }}"
           class="pill {{ $activeSlug===$cat->slug ? 'active' : '' }}">{{ $cat->name }}</a>
      @endforeach
    </div>
    @endif

    @if($services->isEmpty())
      <div class="empty-state">
        <p>Không tìm thấy dịch vụ phù hợp.</p>
        <a href="{{ route('services.index') }}" class="btn btn-outline">Xem tất cả dịch vụ</a>
      </div>
    @else
    <div class="product-grid">
      @foreach($services as $service)
      <article class="product-card fade-in">
        <a href="{{ route('service.show', $service->slug) }}" class="product-img">
          @if($service->image)
            <img src="{{ $service->image_url }}" alt="{{ $service->name }}" loading="lazy">
          @endif
        </a>
        <div class="product-body">
          @if($service->category)<div class="product-cat">{{ $service->category->name }}</div>@endif
          <h3><a href="{{ route('service.show', $service->slug) }}">{{ $service->name }}</a></h3>
          <p class="product-desc">{{ \Illuminate\Support\Str::limit(strip_tags(str_replace("\n", ' ', (string) $service->short_desc)), 90) }}</p>
          <div class="product-price">
            @if($service->price > 0)
              {{ number_format($service->price,0,',','.') }}đ
            @else
              <span style="color:var(--primary)">Liên hệ</span>
            @endif
          </div>
        </div>
      </article>
      @endforeach
    </div>
    <div class="pagination-wrap">{{ $services->links('pagination::theme') }}</div>
    @endif
  </div>
</div>
@endsection
