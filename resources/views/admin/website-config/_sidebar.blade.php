@php
  $wc = request()->route()->getName();
  $wcLinks = [
    ['admin.website-config.info',        'Thông tin website'],
    ['admin.website-config.general',     'Cấu hình chung'],
    ['admin.website-config.pagination',  'Cấu hình phân trang'],
    ['admin.website-config.maintenance', 'Bảo trì website'],
  ];
@endphp
<aside class="wc-sidebar">
  <ul>
    @foreach($wcLinks as [$r, $label])
      <li>
        <a href="{{ route($r) }}" class="{{ $wc === $r ? 'active' : '' }}">{{ $label }}</a>
      </li>
    @endforeach
  </ul>
</aside>
