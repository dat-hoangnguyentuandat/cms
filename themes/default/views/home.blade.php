@extends('layouts.app')
@section('title', 'Trang chủ')

@php
  $homepageBlocks = $homepageBlocks ?? collect();
  $mainBlocks = $homepageBlocks->get('main', collect());
  $sidebarBlocks = $homepageBlocks->get('sidebar', collect());

  // Fallback: render a sensible default layout if no blocks configured yet.
  $fallbackBlocks = collect([
      (object) ['id' => 'fb-slider', 'block_type' => 'slider',                'style_key' => 'slider_1',                'zone' => 'main', 'title' => null, 'settings' => []],
      (object) ['id' => 'fb-serv',   'block_type' => 'service-section',       'style_key' => 'service_section_1',       'zone' => 'main', 'title' => 'Dịch vụ nổi bật', 'settings' => []],
      (object) ['id' => 'fb-prod',   'block_type' => 'product-group',         'style_key' => 'product_group_1',         'zone' => 'main', 'title' => 'Sản phẩm nổi bật', 'settings' => []],
      (object) ['id' => 'fb-test',   'block_type' => 'customer-testimonials', 'style_key' => 'customer-testimonials_4', 'zone' => 'main', 'title' => 'Khách hàng nói gì', 'settings' => ['content_source' => 'import']],
      (object) ['id' => 'fb-post',   'block_type' => 'post-group',            'style_key' => 'post_group_1',            'zone' => 'main', 'title' => 'Tin tức mới nhất', 'settings' => []],
      (object) ['id' => 'fb-news',   'block_type' => 'newsletter',            'style_key' => 'newsletter_1',            'zone' => 'main', 'title' => 'Sẵn sàng bắt đầu?', 'settings' => []],
  ]);
  $renderBlocks = $mainBlocks->isNotEmpty() ? $mainBlocks : $fallbackBlocks;
@endphp

@section('content')
  @if($sidebarBlocks->isNotEmpty())
    <div class="home-with-sidebar container">
      <div class="home-main">
        @foreach($renderBlocks as $block)
          @include('partials.homepage-block', ['block' => $block])
        @endforeach
      </div>
      <aside class="home-sidebar">
        @foreach($sidebarBlocks as $block)
          @include('widgets.render', ['block' => $block])
        @endforeach
      </aside>
    </div>
  @else
    @foreach($renderBlocks as $block)
      @include('partials.homepage-block', ['block' => $block])
    @endforeach
  @endif
@endsection
