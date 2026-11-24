<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Trang chủ') · {{ \App\Models\Setting::get('site_name', 'Website') }}</title>
  <meta name="description" content="@yield('description', strip_tags(\App\Models\Setting::get('site_desc', '')))">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  @php
    $siteLogo = \App\Models\Setting::get('site_logo');
    $faviconUrl = $siteLogo ? asset('storage/' . $siteLogo) : theme_asset('images/favicon.svg');
  @endphp
  <link rel="icon" href="{{ $faviconUrl }}">
  <link rel="stylesheet" href="{{ versioned_theme_asset('css/style.css') }}">
  @stack('css')
</head>
<body>
@php
  use App\Models\Setting;
  $siteName     = Setting::get('site_name', 'Website');
  $siteTag      = Setting::get('site_tagline', '');
  $siteDesc     = Setting::get('site_desc', '');
  $hotline      = Setting::get('hotline', '0909 123 456');
  $hotlineTel   = preg_replace('/\s+/', '', $hotline);
  $zalo         = Setting::get('zalo', '');
  $address      = Setting::get('address', '');
  $email        = Setting::get('email', '');
  $workingHours = Setting::get('working_hours', '');
  $facebook     = Setting::get('facebook_url', '#');
  $youtube      = Setting::get('youtube_url', '#');

  $navItems = \App\Models\MenuItem::whereNull('parent_id')
      ->where('is_active', true)
      ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
      ->orderBy('sort_order')
      ->get();

  $layoutBlocks = isset($homepageBlocks) ? $homepageBlocks : collect();
  $headerBlocks = $layoutBlocks->get('header', collect());
  $footerBlocks = collect(['footer_top','footer_1','footer_2','footer_3','footer_bottom'])
      ->flatMap(fn($zone) => $layoutBlocks->get($zone, collect()));
  $floatLeftBlocks  = $layoutBlocks->get('float_left', collect());
  $floatRightBlocks = $layoutBlocks->get('float_right', collect());
  $shouldRenderHeader = !$layoutBlocks->count() || $headerBlocks->isEmpty();
@endphp

@if($shouldRenderHeader)
  @include('partials.header', [
    'siteName'   => $siteName,
    'siteTag'    => $siteTag,
    'hotline'    => $hotline,
    'hotlineTel' => $hotlineTel,
    'navItems'   => $navItems,
  ])
@else
  @foreach($headerBlocks as $block)
    @include('widgets.render', ['block' => $block])
  @endforeach
@endif

@yield('content')

@foreach($floatLeftBlocks as $block)
  @include('widgets.render', ['block' => $block])
@endforeach
@foreach($floatRightBlocks as $block)
  @include('widgets.render', ['block' => $block])
@endforeach

@if(!$layoutBlocks->count() || $footerBlocks->isNotEmpty())
  @include('partials.footer', [
    'siteName'     => $siteName,
    'siteTag'      => $siteTag,
    'siteDesc'     => $siteDesc,
    'hotline'      => $hotline,
    'hotlineTel'   => $hotlineTel,
    'email'        => $email,
    'address'      => $address,
    'workingHours' => $workingHours,
    'facebook'     => $facebook,
    'youtube'      => $youtube,
    'zalo'         => $zalo,
    'layoutBlocks' => $layoutBlocks,
  ])
@endif

<script src="{{ versioned_theme_asset('js/app.js') }}"></script>
@stack('js')
</body>
</html>
