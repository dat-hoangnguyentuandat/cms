@php
  $_settings = $block->settings ?? [];
  $_displayMode = $_settings['display_mode'] ?? 'show';
  $_uid = 'service-block-' . $block->id;
  $_layout = $_settings['layout'] ?? 'image_top';
  $_columns = max(1, min(6, (int) ($_settings['desktop_items'] ?? 3)));
  $_mobileColumns = max(1, min(3, (int) ($_settings['mobile_items'] ?? 1)));
  $_iconPosition = $_settings['icon_position'] ?? 'top';
  $_imagePosition = $_settings['image_position'] ?? 'top';
  $_iconShape = $_settings['icon_shape'] ?? 'circle';
  $_iconColor = $_settings['icon_color'] ?? '#1683f7';
  $_titleColor = $_settings['title_color'] ?? '#1f2937';
  $_textColor = $_settings['text_color'] ?? '#6b7280';
  $_backgroundColor = $_settings['background_color'] ?? '#ffffff';
  $_lineColor = $_settings['line_color'] ?? '#cfe3ff';
  $_headingColor = $_settings['heading_color'] ?? $_iconColor;
  $_headingSize = max(10, min(72, (int) ($_settings['heading_size'] ?? 18)));
  $_sectionTitle = trim((string) ($_settings['title'] ?? ($block->title ?: 'Khối dịch vụ')));
  $_titleUrl = trim((string) ($_settings['title_url'] ?? ''));
  $_subtitle = trim((string) ($_settings['subtitle'] ?? ''));
  $_contentSource = $_settings['content_source'] ?? 'manual';
  $_sourceType = $_settings['source_type'] ?? 'service';
  $_sourceLimit = max(1, min(12, (int) ($_settings['source_limit'] ?? 6)));
  $_assetUrl = function ($_path) {
    $_path = trim((string) $_path);
    if ($_path === '') return '';
    if (preg_match('/^https?:\/\//i', $_path) || str_starts_with($_path, '/')) return $_path;
    if (str_starts_with($_path, 'theme-assets/')) return asset($_path);
    return asset('storage/' . $_path);
  };
  $_normalizeImported = function ($_row) use ($_sourceType) {
    if ($_sourceType === 'product') {
      return [
        'title' => $_row->name ?? '',
        'url' => !empty($_row->slug) ? route('product.show', $_row->slug) : '',
        'description' => \Illuminate\Support\Str::limit(strip_tags((string) ($_row->short_desc ?: $_row->description)), 140),
        'image' => $_row->image_url ?? '',
        'icon_image' => null,
      ];
    }
    if ($_sourceType === 'news') {
      return [
        'title' => $_row->title ?? '',
        'url' => !empty($_row->slug) ? route('news.show', $_row->slug) : '',
        'description' => \Illuminate\Support\Str::limit(strip_tags((string) ($_row->excerpt ?: $_row->content)), 140),
        'image' => $_row->image_url ?? '',
        'icon_image' => null,
      ];
    }
    return [
      'title' => $_row->name ?? '',
      'url' => !empty($_row->slug) ? route('service.show', $_row->slug) : '',
      'description' => \Illuminate\Support\Str::limit(strip_tags((string) ($_row->short_desc ?: $_row->description)), 140),
      'image' => $_row->image_url ?? '',
      'icon_image' => null,
    ];
  };
  if ($_contentSource === 'import') {
    if ($_sourceType === 'product') {
      $_items = \App\Models\Product::where('is_active', true)->orderByDesc('is_featured')->orderByDesc('id')->limit($_sourceLimit)->get()->map($_normalizeImported);
    } elseif ($_sourceType === 'news') {
      $_items = \App\Models\News::where('is_published', true)->orderByDesc('id')->limit($_sourceLimit)->get()->map($_normalizeImported);
    } else {
      $_items = \App\Models\Service::where('is_active', true)->orderByDesc('is_featured')->orderByDesc('id')->limit($_sourceLimit)->get()->map($_normalizeImported);
    }
  } else {
    $_items = collect($_settings['items'] ?? []);
  }
  $_items = $_items->filter(fn($_item) => trim((string)($_item['title'] ?? '')) !== '')->values();
@endphp

@if($_displayMode !== 'hide')
<section class="service-block-widget {{ 'service-block-' . $_layout }}" id="{{ $_uid }}" style="--sb-cols:{{ $_columns }};--sb-mobile-cols:{{ $_mobileColumns }};--sb-icon:{{ $_iconColor }};--sb-title:{{ $_titleColor }};--sb-text:{{ $_textColor }};--sb-bg:{{ $_backgroundColor }};--sb-line:{{ $_lineColor }};--sb-heading:{{ $_headingColor }};--sb-heading-size:{{ $_headingSize }}px;">
  <div class="container">
    @if($_sectionTitle || $_subtitle)
      <div class="service-block-head">
        @if($_sectionTitle)
          @if($_titleUrl)
            <a href="{{ $_titleUrl }}" class="service-block-heading">{{ $_sectionTitle }}</a>
          @else
            <h2 class="service-block-heading">{{ $_sectionTitle }}</h2>
          @endif
        @endif
        @if($_subtitle)
          <div class="service-block-subtitle">{{ $_subtitle }}</div>
        @endif
      </div>
    @endif

    @if($_items->isNotEmpty())
      @if(in_array($_layout, ['image_side', 'image_diamond', 'center_image_features'], true))
        <div class="service-block-side">
          @if(($_settings['side_image'] ?? null))
            <div class="service-block-side-image {{ $_imagePosition === 'left' ? 'is-left' : 'is-right' }}">
              <img src="{{ asset('storage/' . $_settings['side_image']) }}" alt="{{ $_sectionTitle }}" loading="lazy">
            </div>
          @endif
          <div class="service-block-side-list">
            @foreach($_items as $_i => $_item)
              @php $_url = trim((string)($_item['url'] ?? '')); @endphp
              <a class="service-block-row" href="{{ $_url ?: 'javascript:void(0)' }}">
                <span class="service-block-row-icon {{ $_iconShape }}">
                  @if(($_item['icon_image'] ?? null))
                    <img src="{{ $_assetUrl($_item['icon_image']) }}" alt="{{ $_item['title'] }}" loading="lazy">
                  @else
                    <i>{{ $_i + 1 }}</i>
                  @endif
                </span>
                <span class="service-block-row-content">
                  <strong>{{ $_item['title'] }}</strong>
                  @if(trim((string)($_item['description'] ?? '')))
                    <small>{{ $_item['description'] }}</small>
                  @endif
                </span>
              </a>
            @endforeach
          </div>
        </div>
      @else
        <div class="service-block-grid">
          @foreach($_items as $_i => $_item)
            @php $_url = trim((string)($_item['url'] ?? '')); @endphp
            <a class="service-block-card {{ ($_iconPosition === 'line' || $_layout === 'icon_line') ? 'icon-line' : '' }}" href="{{ $_url ?: 'javascript:void(0)' }}">
              @if(($_item['image'] ?? null) && in_array($_layout, ['image_top', 'image_card', 'product_cards', 'overlay', 'color_blocks'], true))
                <span class="service-block-image"><img src="{{ $_assetUrl($_item['image']) }}" alt="{{ $_item['title'] }}" loading="lazy"></span>
              @endif
              <span class="service-block-content">
                <span class="service-block-icon {{ $_iconShape }}">
                  @if($_layout === 'number_icon')
                    <i>{{ $_i + 1 }}</i>
                  @elseif(($_item['icon_image'] ?? null))
                    <img src="{{ $_assetUrl($_item['icon_image']) }}" alt="{{ $_item['title'] }}" loading="lazy">
                  @else
                    <i>{{ $_i + 1 }}</i>
                  @endif
                </span>
                <strong>{{ $_item['title'] }}</strong>
                @if(trim((string)($_item['description'] ?? '')))
                  @if($_layout === 'product_cards')
                    <ul class="service-block-bullets">
                      @foreach(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $_item['description']))) as $_bullet)
                        <li>{{ $_bullet }}</li>
                      @endforeach
                    </ul>
                  @else
                    <small>{{ $_item['description'] }}</small>
                  @endif
                @endif
              </span>
            </a>
          @endforeach
        </div>
      @endif
    @else
      <p style="text-align:center;color:var(--sb-text);">Chưa có nội dung dịch vụ.</p>
    @endif
  </div>
</section>

@once
  @push('css')
  <style>
  .service-block-widget{padding:34px 0;background:var(--sb-bg)}
  .service-block-head{text-align:center;margin-bottom:24px}.service-block-heading{display:inline-block;margin:0;color:var(--sb-heading);font-size:var(--sb-heading-size);font-weight:600;text-decoration:none}.service-block-head-line{display:block;width:90px;height:1px;background:var(--sb-line);margin:8px auto 0;position:relative}.service-block-head-line:after{content:"";position:absolute;left:50%;top:-4px;width:9px;height:9px;background:var(--sb-icon);border-radius:50%;transform:translateX(-50%)}.service-block-subtitle{margin-top:12px;color:var(--sb-text);font-size:14px}
  .service-block-grid{display:grid;grid-template-columns:repeat(var(--sb-cols),minmax(0,1fr));gap:20px}.service-block-card{display:block;text-decoration:none;color:inherit;text-align:center;overflow:hidden;position:relative}.service-block-image{display:block;aspect-ratio:4/3;overflow:hidden;margin-bottom:0;background:#eef2f7}.service-block-image img{width:100%;height:100%;object-fit:cover;transition:.35s}.service-block-card:hover .service-block-image img{transform:scale(1.05)}.service-block-content{display:flex;flex-direction:column;align-items:center;padding:18px 10px 8px;border-top:1px solid var(--sb-line);position:relative}.service-block-icon,.service-block-row-icon{width:42px;height:42px;display:flex;align-items:center;justify-content:center;background:var(--sb-icon);color:#fff;margin-bottom:10px;flex-shrink:0}.service-block-icon.circle,.service-block-row-icon.circle{border-radius:50%}.service-block-icon.square,.service-block-row-icon.square{border-radius:6px}.service-block-icon.none,.service-block-row-icon.none{background:transparent;color:var(--sb-icon)}.service-block-icon img,.service-block-row-icon img{width:100%;height:100%;object-fit:cover;border-radius:inherit}.service-block-icon i,.service-block-row-icon i{font-style:normal;font-weight:700;font-size:18px}.service-block-card strong,.service-block-row strong{display:block;color:var(--sb-title);font-size:13px;font-weight:700;text-transform:uppercase;line-height:1.4}.service-block-card small,.service-block-row small{display:block;margin-top:4px;color:var(--sb-text);font-size:12px;line-height:1.45}.service-block-card:hover strong,.service-block-row:hover strong{color:var(--sb-icon)}
  .service-block-card.icon-line .service-block-content{padding-top:22px}.service-block-card.icon-line .service-block-icon{position:absolute;top:0;transform:translateY(-50%);margin:0;width:34px;height:34px}.service-block-card.icon-line .service-block-icon i{font-size:16px}
  .service-block-icons_only .service-block-grid,.service-block-simple_horizontal .service-block-grid{gap:16px}.service-block-icons_only .service-block-content,.service-block-simple_horizontal .service-block-content{border-top:0;padding:14px 8px}.service-block-icons_only .service-block-icon{background:transparent;color:var(--sb-icon);width:52px;height:52px}.service-block-icons_only .service-block-icon i{font-size:26px}.service-block-simple_horizontal .service-block-card{border-right:1px solid var(--sb-line)}.service-block-simple_horizontal .service-block-card:last-child{border-right:0}
  .service-block-icon_left .service-block-card{text-align:left}.service-block-icon_left .service-block-content{flex-direction:row;align-items:flex-start;gap:12px;border-top:0}.service-block-icon_left .service-block-icon{margin:0}.service-block-icon_left .service-block-content strong,.service-block-icon_left .service-block-content small{text-align:left}
  .service-block-icon_card .service-block-card{background:#fff;border:1px solid var(--sb-line);border-radius:12px;box-shadow:0 10px 24px rgba(15,23,42,.06);padding-top:18px}.service-block-icon_card .service-block-content{border-top:0;padding:18px 16px 20px}.service-block-icon_card .service-block-icon{width:56px;height:56px;box-shadow:0 8px 18px rgba(22,131,247,.18)}
  .service-block-image_card .service-block-card{background:#111;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 8px 22px rgba(15,23,42,.07);text-align:left;min-height:220px}.service-block-image_card .service-block-image{position:absolute;inset:0;aspect-ratio:auto}.service-block-image_card .service-block-image:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,.05),rgba(0,0,0,.62))}.service-block-image_card .service-block-content{position:relative;z-index:1;min-height:220px;justify-content:flex-end;align-items:flex-start;border-top:0;padding:22px}.service-block-image_card .service-block-icon{display:none}.service-block-image_card strong{font-size:16px;color:#fff}.service-block-image_card small{font-size:13px;color:rgba(255,255,255,.88)}
  .service-block-number_icon .service-block-icon{background:#fff;color:var(--sb-icon);border:2px solid var(--sb-icon);font-size:16px}.service-block-number_icon .service-block-card{border-bottom:2px solid var(--sb-line);padding-bottom:10px}
  .service-block-product_cards .service-block-grid{gap:18px}.service-block-product_cards .service-block-card{background:#fff;border:1px solid #e3e8ef;border-radius:6px;box-shadow:0 4px 14px rgba(15,23,42,.06);text-align:left}.service-block-product_cards .service-block-image{aspect-ratio:1.55/1;border-bottom:1px solid #e5e7eb}.service-block-product_cards .service-block-content{align-items:flex-start;border-top:0;padding:14px 16px 16px}.service-block-product_cards .service-block-icon{display:none}.service-block-product_cards strong{font-size:14px;color:var(--sb-title);margin-bottom:9px}.service-block-bullets{list-style:none;margin:0 0 12px;padding:0;color:var(--sb-text);font-size:12px;line-height:1.55}.service-block-bullets li{position:relative;padding-left:14px;margin-bottom:3px}.service-block-bullets li:before{content:'✓';position:absolute;left:0;top:0;color:var(--sb-icon);font-size:11px}.service-block-more{font-style:normal;color:var(--sb-icon);font-size:12px;font-weight:700;margin-top:auto}
  .service-block-overlay .service-block-card,.service-block-color_blocks .service-block-card{position:relative;min-height:170px;background:#111}.service-block-overlay .service-block-image,.service-block-color_blocks .service-block-image{position:absolute;inset:0;aspect-ratio:auto;opacity:.72}.service-block-overlay .service-block-content,.service-block-color_blocks .service-block-content{position:relative;z-index:1;height:100%;min-height:170px;justify-content:flex-end;align-items:flex-start;text-align:left;border:0;padding:22px;background:linear-gradient(180deg,rgba(0,0,0,.05),rgba(0,0,0,.55))}.service-block-overlay strong,.service-block-overlay small,.service-block-color_blocks strong,.service-block-color_blocks small{color:#fff}.service-block-overlay .service-block-icon,.service-block-color_blocks .service-block-icon{background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.35)}.service-block-color_blocks .service-block-card:nth-child(4n+1){background:#317f67}.service-block-color_blocks .service-block-card:nth-child(4n+2){background:#667c26}.service-block-color_blocks .service-block-card:nth-child(4n+3){background:#986b3c}.service-block-color_blocks .service-block-card:nth-child(4n+4){background:#a34859}
  .service-block-side{display:grid;grid-template-columns:1fr 1fr;gap:28px;align-items:center}.service-block-side-image.is-left{order:-1}.service-block-side-image.is-right{order:2}.service-block-side-image img{width:100%;height:auto;display:block}.service-block-side-list{display:grid;gap:14px}.service-block-row{display:flex;gap:12px;align-items:flex-start;text-decoration:none;color:inherit}.service-block-row-icon{width:36px;height:36px;margin:0}.service-block-row-icon i{font-size:15px}.service-block-row-content{display:block;text-align:left}.service-block-image_diamond .service-block-side-image img{clip-path:polygon(50% 0,100% 50%,50% 100%,0 50%);padding:8%;background:#eef7ff}.service-block-center_image_features .service-block-side{grid-template-columns:1fr minmax(220px,.8fr) 1fr}.service-block-center_image_features .service-block-side-image{order:1}.service-block-center_image_features .service-block-side-list{grid-template-columns:1fr 1fr;grid-column:1/4;order:2}.service-block-center_image_features .service-block-row:nth-child(odd){text-align:right;flex-direction:row-reverse}.service-block-center_image_features .service-block-row:nth-child(odd) .service-block-row-content{text-align:right}
  @media(max-width:767px){.service-block-grid{grid-template-columns:repeat(var(--sb-mobile-cols),minmax(0,1fr));gap:14px}.service-block-side,.service-block-center_image_features .service-block-side{grid-template-columns:1fr}.service-block-side-image.is-right,.service-block-center_image_features .service-block-side-image{order:0}.service-block-center_image_features .service-block-side-list{grid-template-columns:1fr;grid-column:auto}.service-block-center_image_features .service-block-row:nth-child(odd){text-align:left;flex-direction:row}.service-block-center_image_features .service-block-row:nth-child(odd) .service-block-row-content{text-align:left}}
  </style>
  @endpush
@endonce
@endif
