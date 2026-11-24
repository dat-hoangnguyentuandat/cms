@php
  $current = request()->route()->getName();
  $pluginLinks = app(\App\Services\Plugin\PluginManager::class)->list()
      ->filter(fn ($plugin) => $plugin['is_active'] && ! empty($plugin['manifest']['admin_menu']))
      ->flatMap(function ($plugin) {
          return collect($plugin['manifest']['admin_menu'])->map(function ($item) use ($plugin) {
              return [
                  'route' => $item['route'] ?? null,
                  'label' => $item['label'] ?? $plugin['title'],
                  'permission' => $item['permission'] ?? null,
              ];
          });
      })
      ->filter(fn ($item) => ! empty($item['route']))
      ->values();
@endphp

<aside class="wc-sidebar">
  <ul>
    <li>
      <a href="{{ route('admin.plugins.index') }}" class="{{ $current === 'admin.plugins.index' ? 'active' : '' }}">Danh sách plugin</a>
    </li>
    @foreach($pluginLinks as $item)
      @php
        $canView = empty($item['permission'])
          || (($_adminUser ?? null)?->role === 'admin')
          || (($_adminUser ?? null)?->hasAdminPermission($item['permission']));
      @endphp
      @if($canView)
        <li>
          <a href="{{ route($item['route']) }}" class="{{ str_starts_with($current ?? '', preg_replace('/\.(index|create|edit)$/', '', $item['route'])) ? 'active' : '' }}">{{ $item['label'] }}</a>
        </li>
      @endif
    @endforeach
  </ul>
</aside>
