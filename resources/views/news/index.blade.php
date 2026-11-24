@extends('layouts.app')
@section('title','Tin tức')
@push('css')<link rel="stylesheet" href="{{ asset('css/shop.css') }}">@endpush

@section('content')
<main class="shop-main">
  <div class="container">
    <nav class="breadcrumb">
      <a href="{{ route('home') }}">Trang chủ</a> <span>›</span>
      <span>Tin tức</span>
    </nav>

    <header class="news-page-head">
      <div class="section-eyebrow" style="justify-content:flex-start">Tin tức</div>
      <h1 class="page-title">Tin tức & kiến thức về mũ bảo hiểm in logo</h1>
    </header>

    @if($categories->isNotEmpty())
    <div class="news-cat-filter">
      <a href="{{ route('news.index') }}" class="news-cat-btn {{ !$cat ? 'active' : '' }}">Tất cả</a>
      @foreach($categories as $c)
        <a href="{{ route('news.index', ['danh-muc' => $c->slug]) }}"
           class="news-cat-btn {{ $cat && $cat->id === $c->id ? 'active' : '' }}">
          {{ $c->name }}
        </a>
      @endforeach
    </div>
    @endif

    @if($posts->isEmpty())
      <div class="shop-empty"><p>Chưa có bài viết nào{{ $cat ? ' trong danh mục này' : '' }}.</p></div>
    @else
      <div class="project-grid">
        @foreach($posts as $p)
        <article class="project-card">
          @if($p->image)<img src="{{ $p->image_url }}" alt="{{ $p->title }}" loading="lazy">@endif
          <div>
            @if($p->category)
              <div class="news-card-cat">{{ $p->category->name }}</div>
            @endif
            <h3><a href="{{ route('news.show', $p->slug) }}">{{ $p->title }}</a></h3>
            <div class="rich-content">{!! cms_content($p->excerpt, $p) !!}</div>
            <div class="news-card-footer">
              <small>{{ $p->created_at->format('d/m/Y') }} · {{ number_format($p->views) }} lượt xem</small>
              <a href="{{ route('news.show', $p->slug) }}" class="news-read-more">Đọc thêm →</a>
            </div>
          </div>
        </article>
        @endforeach
      </div>
      @if($posts->hasPages())
        <div class="shop-pagination">{{ $posts->links() }}</div>
      @endif
    @endif
  </div>
</main>
@endsection
