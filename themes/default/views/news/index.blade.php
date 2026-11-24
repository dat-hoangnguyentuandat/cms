@extends('layouts.app')
@section('title','Tin tức')

@section('content')
<div class="page">
  <div class="container">
    <nav class="breadcrumb"><a href="{{ route('home') }}">Trang chủ</a> · <span>Tin tức</span></nav>
    <h1 class="page-title">Tin tức & bài viết</h1>
    <p class="page-desc">Cập nhật tin tức mới nhất, mẹo hay và chia sẻ từ đội ngũ chuyên gia.</p>

    @if($categories->isNotEmpty())
    <div class="pill-group">
      <a href="{{ route('news.index') }}" class="pill {{ !$cat ? 'active' : '' }}">Tất cả</a>
      @foreach($categories as $c)
        <a href="{{ route('news.index', ['danh-muc' => $c->slug]) }}"
           class="pill {{ $cat && $cat->id===$c->id ? 'active' : '' }}">{{ $c->name }}</a>
      @endforeach
    </div>
    @endif

    @if($posts->isEmpty())
      <div class="empty-state"><p>Chưa có bài viết nào.</p></div>
    @else
    <div class="news-grid">
      @foreach($posts as $p)
      <article class="news-card fade-in">
        @if($p->image)
        <a href="{{ route('news.show', $p->slug) }}" class="news-img">
          <img src="{{ $p->image_url }}" alt="{{ $p->title }}" loading="lazy">
        </a>
        @endif
        <div class="news-body">
          <div class="news-meta">{{ $p->created_at->format('d/m/Y') }} @if($p->category)· {{ $p->category->name }}@endif</div>
          <h3><a href="{{ route('news.show', $p->slug) }}">{{ $p->title }}</a></h3>
          <p>{{ Str::limit(strip_tags($p->excerpt), 120) }}</p>
        </div>
      </article>
      @endforeach
    </div>
    @endif
  </div>
</div>
@endsection
