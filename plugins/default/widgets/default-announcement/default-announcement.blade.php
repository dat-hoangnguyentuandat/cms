@php
  $settings = $block->settings ?? [];
  $eyebrow = $settings['eyebrow'] ?? 'Plugin Mặc định';
  $title = $block->title ?: ($settings['title'] ?? 'Widget mẫu từ plugin');
  $content = $settings['content'] ?? 'Block này được cấu hình trực tiếp trong Bố cục trang chủ bằng settings_schema.';
  $buttonText = $settings['button_text'] ?? 'Liên hệ';
  $buttonUrl = $settings['button_url'] ?? '/lien-he';
  $backgroundColor = $settings['background_color'] ?? '#eff6ff';
  $accentColor = $settings['accent_color'] ?? '#2563eb';
@endphp

@include('plugin-default::partials.assets')

<section class="default-plugin-announcement" style="--default-plugin-bg: {{ $backgroundColor }}; --default-plugin-accent: {{ $accentColor }};">
  <div class="default-plugin-announcement__inner">
    <div>
      <p class="default-plugin-announcement__eyebrow">{{ $eyebrow }}</p>
      <h2>{{ $title }}</h2>
      <div class="default-plugin-announcement__content">{!! cms_content($content, $block) !!}</div>
    </div>
    @if($buttonText && $buttonUrl)
      <a class="default-plugin-announcement__button" href="{{ $buttonUrl }}">{{ $buttonText }}</a>
    @endif
  </div>
</section>
