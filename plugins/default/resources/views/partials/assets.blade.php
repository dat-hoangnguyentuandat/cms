@once
  @php
    $base = 'plugin-assets/default';
    $cssFile = public_path($base.'/css/default.css');
    $jsFile = public_path($base.'/js/default.js');
    $cssVersion = is_file($cssFile) ? filemtime($cssFile) : '1';
    $jsVersion = is_file($jsFile) ? filemtime($jsFile) : '1';
  @endphp
  <link rel="stylesheet" href="{{ asset($base.'/css/default.css') }}?v={{ $cssVersion }}">
  <script src="{{ asset($base.'/js/default.js') }}?v={{ $jsVersion }}" defer></script>
@endonce
