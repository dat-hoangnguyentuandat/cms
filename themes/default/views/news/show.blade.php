@extends('layouts.app')
@section('title', $post->title)
@section('description', strip_tags($post->excerpt))

@section('content')
<div class="page">
  <div class="container" style="max-width:880px">
    <nav class="breadcrumb">
      <a href="{{ route('home') }}">Trang chủ</a> ·
      <a href="{{ route('news.index') }}">Tin tức</a> ·
      <span>{{ $post->title }}</span>
    </nav>

    @if($post->category)
      <div class="product-cat" style="margin-bottom:8px">{{ $post->category->name }}</div>
    @endif
    <h1 class="page-title">{{ $post->title }}</h1>
    <div class="news-meta" style="margin-bottom:30px">
      {{ $post->created_at->format('d/m/Y') }}
      @if($post->author) · {{ $post->author }}@endif
    </div>

    @if($post->image)
      <img src="{{ $post->image_url }}" alt="{{ $post->title }}" style="width:100%;border-radius:var(--radius);margin-bottom:30px">
    @endif

    @if($post->excerpt)
      <div class="article-excerpt">{!! cms_content($post->excerpt, $post) !!}</div>
    @endif

    <div class="article-body">{!! cms_content($post->content, $post) !!}</div>

    <div style="margin-top:50px">
      <div class="cta-banner">
        <h2>Bạn quan tâm đến dịch vụ?</h2>
        <p>Liên hệ ngay để được tư vấn miễn phí.</p>
        <a href="{{ route('contact') }}" class="btn btn-primary btn-lg">Liên hệ ngay</a>
      </div>
    </div>

    @if($other->count())
    <div style="margin-top:60px">
      <h2 style="font-size:22px;margin:0 0 24px">Bài viết khác</h2>
      <div class="news-grid">
        @foreach($other as $o)
        <article class="news-card">
          @if($o->image)
          <a href="{{ route('news.show', $o->slug) }}" class="news-img">
            <img src="{{ $o->image_url }}" alt="{{ $o->title }}" loading="lazy">
          </a>
          @endif
          <div class="news-body">
            <h3><a href="{{ route('news.show', $o->slug) }}">{{ $o->title }}</a></h3>
          </div>
        </article>
        @endforeach
      </div>
    </div>
    @endif
  </div>
</div>
@endsection
