@extends('layouts.app')
@section('title', $post->title)
@section('description', strip_tags($post->excerpt))
@push('css')<link rel="stylesheet" href="{{ asset('css/shop.css') }}">@endpush

@section('content')
@php $hotline = \App\Models\Setting::get('hotline','0909 123 456'); @endphp
<main class="shop-main">
  <div class="container">
    <nav class="breadcrumb">
      <a href="{{ route('home') }}">Trang chủ</a> <span>›</span>
      <a href="{{ route('news.index') }}">Tin tức</a> <span>›</span>
      @if($post->category)
        <a href="{{ route('news.index', ['danh-muc' => $post->category->slug]) }}">{{ $post->category->name }}</a> <span>›</span>
      @endif
      <span>{{ $post->title }}</span>
    </nav>
    <article class="news-detail">
      <h1 class="page-title">{{ $post->title }}</h1>
      <div class="news-meta">
        @if($post->category)
          <a href="{{ route('news.index', ['danh-muc' => $post->category->slug]) }}" class="news-card-cat">{{ $post->category->name }}</a> ·
        @endif
        {{ $post->created_at->format('d/m/Y') }}
        @if($post->author) · Tác giả: <b>{{ $post->author }}</b>@endif
        · {{ number_format($post->views) }} lượt xem
      </div>
      @if($post->image)<img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="news-cover">@endif
      @if($post->excerpt)<div class="news-excerpt rich-content">{!! cms_content($post->excerpt, $post) !!}</div>@endif
      <div class="news-content rich-content">{!! cms_content($post->content, $post) !!}</div>

      <div class="news-cta-box">
        <h3>Cần tư vấn mũ bảo hiểm in logo?</h3>
        <p>Liên hệ ngay để được báo giá và tư vấn miễn phí trong 2 giờ làm việc.</p>
        <div class="news-cta-btns">
          <a href="{{ route('contact') }}" class="btn btn-primary">Nhận báo giá ngay</a>
          <a href="tel:{{ preg_replace('/\s+/','',$hotline) }}" class="btn btn-outline-dark">{{ $hotline }}</a>
        </div>
      </div>
    </article>

    @if($other->count())
    <h2 class="news-other-title">Bài viết khác</h2>
    <div class="project-grid">
      @foreach($other as $o)
      <article class="project-card">
        @if($o->image)<img src="{{ $o->image_url }}" alt="{{ $o->title }}" loading="lazy">@endif
        <div>
          <h3><a href="{{ route('news.show', $o->slug) }}">{{ $o->title }}</a></h3>
          <a href="{{ route('news.show', $o->slug) }}" class="news-read-more">Đọc thêm →</a>
        </div>
      </article>
      @endforeach
    </div>
    @endif
  </div>
</main>
@endsection
