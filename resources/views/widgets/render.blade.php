@php
  $type    = $block->block_type;
  $variant = $block->style_key ?: $type;

  // Try theme-specific widget first, fallback to global widget
  $themeName = app()->bound('theme.name') ? app('theme.name') : 'default';
  $themeWidgetPath = base_path('themes/' . $themeName . '/widgets/' . $type . '/' . $variant . '.blade.php');
  $globalWidgetPath = public_path('admin-assets/widgets/' . $type . '/' . $variant . '.blade.php');
  $widgetDef = app(\App\Services\Widget\WidgetRegistry::class)->widget($type);
  $pluginWidgetPath = !empty($widgetDef['plugin'])
    ? base_path('plugins/' . $widgetDef['plugin'] . '/widgets/' . $type . '/' . $variant . '.blade.php')
    : null;

  $file = file_exists($themeWidgetPath)
    ? $themeWidgetPath
    : (file_exists($globalWidgetPath) ? $globalWidgetPath : ($pluginWidgetPath ?: ''));

  $vars = [
    'block'      => $block,
    'siteName'   => $siteName   ?? \App\Models\Setting::get('site_name',''),
    'siteTag'    => $siteTag    ?? \App\Models\Setting::get('site_tagline',''),
    'hotline'    => $hotline    ?? \App\Models\Setting::get('hotline',''),
    'hotlineTel' => $hotlineTel ?? preg_replace('/\s+/', '', \App\Models\Setting::get('hotline','')),
    'email'      => $email      ?? \App\Models\Setting::get('email',''),
    'address'    => $address    ?? \App\Models\Setting::get('address',''),
    'facebook'   => $facebook   ?? \App\Models\Setting::get('facebook_url','#'),
    'youtube'    => $youtube    ?? \App\Models\Setting::get('youtube_url','#'),
    'zalo'       => $zalo       ?? \App\Models\Setting::get('zalo',''),
    'navItems'   => $navItems   ?? collect(),
    'solutionCats' => $solutionCats ?? collect(),
    'products'   => $products   ?? collect(),
    'services'   => $services   ?? collect(),
    'news'       => $news       ?? collect(),
    'categories' => $categories ?? collect(),
  ];
@endphp

@if(file_exists($file))
  {!! cms_apply_hook('cms.widget.rendered', cms_shortcodes(Blade::render(file_get_contents($file), $vars)), $block, $type, $variant) !!}
@endif
