@php
  $_s            = $block->settings ?? [];
  $_displayMode  = $_s['display_mode'] ?? 'show';
  $_title        = $block->title ?: 'Cảm nhận khách hàng';
  $_titleUrl     = $_s['title_url'] ?? '';
  $_subtitle     = $_s['subtitle'] ?? 'Những chia sẻ thực tế từ khách hàng đã tin tưởng và đồng hành cùng chúng tôi.';
  $_layout       = $_s['layout'] ?? 'slider';
  $_bgColor      = $_s['bg_color'] ?? '#f8fafc';
  $_cardBg       = $_s['card_bg'] ?? '#ffffff';
  $_accentColor  = $_s['accent_color'] ?? '#1683f7';
  $_titleColor   = $_s['title_color'] ?? '#1a2340';
  $_textColor    = $_s['text_color'] ?? '#4b5563';
  $_headingSize  = (int) ($_s['heading_size'] ?? 30);
  $_titleSize    = (int) ($_s['title_size'] ?? 15);
  $_contentSize  = (int) ($_s['content_size'] ?? 14);
  $_avatarSize   = (int) ($_s['avatar_size'] ?? 80);
  $_cardRadius   = (int) ($_s['card_radius'] ?? 16);
  $_cardPadding  = (int) ($_s['card_padding'] ?? 28);
  $_sectionPad   = (int) ($_s['section_padding'] ?? 64);
  $_gap          = (int) ($_s['gap'] ?? 20);
  $_cols         = (int) ($_s['desktop_cols'] ?? 3);
  $_mobileCols   = (int) ($_s['mobile_cols'] ?? 1);
  $_maxWidth     = (int) ($_s['max_width'] ?? 960);
  $_starPos      = $_s['star_position'] ?? 'before_content';
  $_showRole     = (bool) ($_s['show_role'] ?? true);
  $_showCompany  = (bool) ($_s['show_company'] ?? true);
  $_showQuote    = (bool) ($_s['show_quote'] ?? false);
  $_autoplay     = (bool) ($_s['autoplay'] ?? false);
  $_autoplaySpeed = (int) ($_s['autoplay_speed'] ?? 5);
  $_contentSource = $_s['content_source'] ?? 'manual';
  $_sourceLimit   = (int) ($_s['source_limit'] ?? 6);
  $_contentLines  = (int) ($_s['content_lines'] ?? 6);

  if ($_contentSource === 'import') {
    $_items = \App\Models\Testimonial::where('is_active', true)
      ->orderBy('sort_order')
      ->orderByDesc('id')
      ->limit(max(1, $_sourceLimit))
      ->get()
      ->map(function ($t) {
        $avatar = $t->reviewer_avatar ?: $t->avatar;
        $isUrl = $avatar && str_starts_with($avatar, 'http');
        // Theme-asset paths are served directly from /theme-assets, not /storage
        if ($avatar && !$isUrl && str_starts_with($avatar, 'theme-assets/')) {
          $avatar = asset($avatar);
          $isUrl = true;
        }
        // Nâng kích thước ảnh Google (mặc định w36-h36 → mờ khi phóng to)
        if ($isUrl && str_contains($avatar, 'googleusercontent.com')) {
          $avatar = preg_replace('/=w\d+-h\d+/', '=w240-h240', $avatar);
          if (!str_contains($avatar, '=w')) {
            $avatar .= (str_contains($avatar, '=') ? '-w240-h240' : '=w240-h240');
          }
        }
        return [
          'name'    => $t->reviewer_name ?: $t->name,
          'role'    => $t->role ?? '',
          'company' => $t->place_name ?? '',
          'content' => $t->content ?? '',
          'rating'  => (int) ($t->rating ?? 5),
          'avatar'  => $avatar,
          'avatar_is_url' => $isUrl,
        ];
      })
      ->all();
  } else {
    $_items = array_values(array_filter($_s['items'] ?? [], fn($i) => !empty($i['name']) || !empty($i['content'])));
  }

  $_uid = 'ct4-' . $block->id;
@endphp

@once
  <style>
    .ct4-section { padding: var(--ct4-section-pad, 64px) 0; }
    .ct4-inner { margin: 0 auto; }
    .ct4-head { text-align: center; margin-bottom: 40px; }
    .ct4-head-title { font-size: var(--ct4-heading-size, 30px); font-weight: 700; color: var(--ct4-title-color, #1a2340); margin: 0 0 12px; line-height: 1.3; }
    .ct4-head-title a { color: inherit; text-decoration: none; }
    .ct4-head-sub { color: var(--ct4-text-color, #6b7280); font-size: 15px; line-height: 1.7; max-width: 600px; margin: 0 auto; }

    .ct4-card { background: var(--ct4-card-bg, #fff); border-radius: var(--ct4-card-radius, 16px); padding: var(--ct4-card-padding, 28px); box-shadow: 0 2px 16px rgba(0,0,0,.08); display: flex; flex-direction: column; align-items: center; text-align: center; gap: 12px; position: relative; box-sizing: border-box; }
    .ct4-avatar-wrap { width: var(--ct4-avatar-size, 80px); height: var(--ct4-avatar-size, 80px); border-radius: 14px; overflow: hidden; background: #f1f5f9; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 2px solid #e5e7eb; box-sizing: border-box; }
    .ct4-avatar-wrap img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .ct4-avatar-initials { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: calc(var(--ct4-avatar-size, 80px) * 0.35); font-weight: 700; color: #fff; }
    .ct4-author-name { font-size: var(--ct4-title-size, 15px); font-weight: 700; color: var(--ct4-title-color, #1a2340); margin: 0; }
    .ct4-author-meta { font-size: calc(var(--ct4-title-size, 15px) * 0.8); color: #9ca3af; margin: 0; }
    .ct4-stars { display: flex; gap: 3px; justify-content: center; }
    .ct4-star { width: 14px; height: 14px; }
    .ct4-content { font-size: var(--ct4-content-size, 14px); color: var(--ct4-text-color, #4b5563); line-height: 1.75; margin: 0; font-style: italic; width: 100%; word-break: break-word; overflow-wrap: break-word; }
    .ct4-section[data-clamp="1"] .ct4-content { display: -webkit-box; -webkit-line-clamp: var(--ct4-content-lines, 6); -webkit-box-orient: vertical; overflow: hidden; }
    .ct4-divider { width: 36px; height: 2px; border-radius: 2px; margin: 0 auto; }
    .ct4-quote-icon { position: absolute; top: 16px; left: 18px; font-size: 48px; line-height: 1; color: var(--ct4-accent); opacity: .12; font-family: serif; }

    .ct4-grid { display: grid; gap: var(--ct4-gap, 20px); }
    .ct4-grid.cols-1 { grid-template-columns: 1fr; max-width: 520px; margin: 0 auto; }
    .ct4-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
    .ct4-grid.cols-3 { grid-template-columns: repeat(3, 1fr); }
    .ct4-grid.cols-4 { grid-template-columns: repeat(4, 1fr); }
    .ct4-grid.cols-5 { grid-template-columns: repeat(5, 1fr); }
    .ct4-grid.cols-6 { grid-template-columns: repeat(6, 1fr); }

    .ct4-slider-outer { position: relative; padding: 0 44px; }
    .ct4-slider-wrap { overflow: hidden; }
    .ct4-slider-track { display: flex; gap: var(--ct4-gap, 20px); transition: transform .4s cubic-bezier(.4,0,.2,1); will-change: transform; }
    .ct4-slider-track .ct4-card { flex: 0 0 calc((100% - var(--ct4-gap, 20px) * (var(--ct4-cols, 3) - 1)) / var(--ct4-cols, 3)); min-width: 0; }
    .ct4-slider-nav { position: absolute; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; border-radius: 50%; border: 1.5px solid #e5e7eb; background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 2; transition: .2s; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
    .ct4-slider-nav:hover { border-color: var(--ct4-accent); }
    .ct4-slider-nav:hover svg { stroke: var(--ct4-accent); }
    .ct4-slider-nav.prev { left: 0; }
    .ct4-slider-nav.next { right: 0; }
    .ct4-slider-nav svg { width: 16px; height: 16px; stroke: #6b7280; fill: none; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; }
    .ct4-dots { display: flex; justify-content: center; gap: 8px; margin-top: 20px; }
    .ct4-dot { width: 8px; height: 8px; border-radius: 50%; background: #d1d5db; border: none; cursor: pointer; padding: 0; transition: .2s; }
    .ct4-dot.active { width: 24px; border-radius: 4px; }

    .ct4-layout-card_minimal .ct4-card { background: transparent; box-shadow: none; border: 1px solid #e5e7eb; }

    .ct4-layout-bubble .ct4-card { padding: 0; background: transparent; box-shadow: none; gap: 0; align-items: flex-start; text-align: left; }
    .ct4-layout-bubble .ct4-bubble-text { background: var(--ct4-card-bg); padding: 22px 24px; border-radius: var(--ct4-card-radius); position: relative; box-shadow: 0 2px 16px rgba(0,0,0,.06); width: 100%; }
    .ct4-layout-bubble .ct4-bubble-text::after { content: ''; position: absolute; bottom: -10px; left: 36px; width: 18px; height: 18px; background: var(--ct4-card-bg); transform: rotate(45deg); box-shadow: 3px 3px 6px rgba(0,0,0,.04); }
    .ct4-layout-bubble .ct4-bubble-author { display: flex; align-items: center; gap: 14px; margin-top: 22px; padding: 0 8px; }
    .ct4-layout-bubble .ct4-bubble-author > div { text-align: left; }

    .ct4-layout-centered .ct4-card { max-width: 720px; margin: 0 auto; padding: calc(var(--ct4-card-padding) + 12px); }
    .ct4-layout-centered .ct4-content { font-size: calc(var(--ct4-content-size, 14px) + 4px); }

    .ct4-layout-compact .ct4-card { flex-direction: row; align-items: flex-start; text-align: left; gap: 16px; }
    .ct4-layout-compact .ct4-card > .ct4-compact-body { flex: 1; display: flex; flex-direction: column; gap: 8px; }
    .ct4-layout-compact .ct4-stars { justify-content: flex-start; }
    .ct4-layout-compact .ct4-divider { display: none; }

    .ct4-layout-featured .ct4-featured-grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: var(--ct4-gap, 20px); align-items: stretch; }
    .ct4-layout-featured .ct4-featured-main .ct4-card { height: 100%; padding: calc(var(--ct4-card-padding) + 12px); }
    .ct4-layout-featured .ct4-featured-list { display: flex; flex-direction: column; gap: var(--ct4-gap, 20px); }
    .ct4-layout-featured .ct4-featured-list .ct4-card { padding: 18px; flex-direction: row; align-items: flex-start; text-align: left; gap: 14px; }
    .ct4-layout-featured .ct4-featured-list .ct4-avatar-wrap { width: calc(var(--ct4-avatar-size) * 0.6); height: calc(var(--ct4-avatar-size) * 0.6); }
    .ct4-layout-featured .ct4-featured-list .ct4-stars { justify-content: flex-start; }
    .ct4-layout-featured .ct4-featured-list .ct4-divider { display: none; }
    .ct4-layout-featured .ct4-featured-list .ct4-author-meta { font-size: 12px; }

    @media (max-width: 900px) {
      .ct4-grid.cols-3, .ct4-grid.cols-4, .ct4-grid.cols-5, .ct4-grid.cols-6 { grid-template-columns: repeat(2, 1fr); }
      .ct4-layout-featured .ct4-featured-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 600px) {
      .ct4-grid.cols-2, .ct4-grid.cols-3, .ct4-grid.cols-4, .ct4-grid.cols-5, .ct4-grid.cols-6 { grid-template-columns: repeat(var(--ct4-mobile-cols, 1), 1fr); }
      .ct4-slider-outer { padding: 0 32px; }
      .ct4-layout-compact .ct4-card { flex-direction: column; align-items: center; text-align: center; }
      .ct4-layout-compact .ct4-stars { justify-content: center; }
    }
  </style>
@endonce

@if($_displayMode !== 'hide' && count($_items) > 0)
<section class="ct4-section ct4-layout-{{ $_layout }}" id="{{ $_uid }}"
  data-clamp="{{ $_contentLines > 0 ? '1' : '0' }}"
  style="background:{{ $_bgColor }};
    --ct4-accent:{{ $_accentColor }};
    --ct4-cols:{{ $_cols }};
    --ct4-mobile-cols:{{ $_mobileCols }};
    --ct4-card-bg:{{ $_cardBg }};
    --ct4-title-color:{{ $_titleColor }};
    --ct4-text-color:{{ $_textColor }};
    --ct4-heading-size:{{ $_headingSize }}px;
    --ct4-title-size:{{ $_titleSize }}px;
    --ct4-content-size:{{ $_contentSize }}px;
    --ct4-avatar-size:{{ $_avatarSize }}px;
    --ct4-card-radius:{{ $_cardRadius }}px;
    --ct4-card-padding:{{ $_cardPadding }}px;
    --ct4-section-pad:{{ $_sectionPad }}px;
    --ct4-gap:{{ $_gap }}px;
    --ct4-content-lines:{{ $_contentLines }}">
  <div class="container">
    <div class="ct4-inner" style="max-width:{{ $_maxWidth }}px">

      @if($_title || $_subtitle)
      <div class="ct4-head">
        @if($_title)
        <h2 class="ct4-head-title">
          @if($_titleUrl)<a href="{{ $_titleUrl }}">{{ $_title }}</a>@else{{ $_title }}@endif
        </h2>
        @endif
        @if($_subtitle)<p class="ct4-head-sub">{{ $_subtitle }}</p>@endif
      </div>
      @endif

      @php
        // helper để render card (dùng lại ở mọi layout)
        $renderCard = function($_item) use ($_accentColor, $_starPos, $_showRole, $_showCompany, $_showQuote, $_layout) {
          $r = (int) ($_item['rating'] ?? 5);
          $av = $_item['avatar'] ?? null;
          $avIsUrl = !empty($_item['avatar_is_url']);
          $initial = mb_strtoupper(mb_substr($_item['name'] ?? 'K', 0, 1));
          $imgFallback = "this.style.display='none';this.nextElementSibling&&(this.nextElementSibling.style.display='flex')";
          $metaParts = array_filter([
            $_showRole    ? ($_item['role'] ?? '')    : '',
            $_showCompany ? ($_item['company'] ?? '') : '',
          ]);
          $starsHtml = '';
          if ($r > 0 && $_starPos !== 'hide') {
            $starsHtml = '<div class="ct4-stars">';
            for ($si = 1; $si <= 5; $si++) {
              $f = $si <= $r ? $_accentColor : '#e5e7eb';
              $starsHtml .= '<svg class="ct4-star" viewBox="0 0 24 24"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26" fill="'.$f.'" stroke="'.$f.'" stroke-width="1"/></svg>';
            }
            $starsHtml .= '</div>';
          }
          ob_start();
          ?>
          <?php if ($_layout === 'bubble'): ?>
            <div class="ct4-card">
              <div class="ct4-bubble-text">
                <?php if ($_showQuote): ?><span class="ct4-quote-icon">"</span><?php endif; ?>
                <?php if ($_starPos === 'top' || $_starPos === 'before_content') echo $starsHtml; ?>
                <?php if (!empty($_item['content'])): ?><p class="ct4-content"><?= e($_item['content']) ?></p><?php endif; ?>
                <?php if ($_starPos === 'bottom') echo $starsHtml; ?>
              </div>
              <div class="ct4-bubble-author">
                <div class="ct4-avatar-wrap">
                  <?php if ($av): ?><img src="<?= $avIsUrl ? e($av) : asset('storage/'.$av) ?>" alt="<?= e($_item['name'] ?? '') ?>" referrerpolicy="no-referrer" loading="lazy" onerror="<?= $imgFallback ?>"><span class="ct4-avatar-initials" style="background:<?= $_accentColor ?>;display:none"><?= $initial ?></span>
                  <?php else: ?><span class="ct4-avatar-initials" style="background:<?= $_accentColor ?>"><?= $initial ?></span><?php endif; ?>
                </div>
                <div>
                  <p class="ct4-author-name"><?= e($_item['name'] ?? '') ?></p>
                  <?php if ($metaParts): ?><p class="ct4-author-meta"><?= e(implode(' · ', $metaParts)) ?></p><?php endif; ?>
                  <?php if ($_starPos === 'after_name') echo $starsHtml; ?>
                </div>
              </div>
            </div>
          <?php elseif ($_layout === 'compact'): ?>
            <div class="ct4-card">
              <div class="ct4-avatar-wrap">
                <?php if ($av): ?><img src="<?= $avIsUrl ? e($av) : asset('storage/'.$av) ?>" alt="<?= e($_item['name'] ?? '') ?>" referrerpolicy="no-referrer" loading="lazy" onerror="<?= $imgFallback ?>"><span class="ct4-avatar-initials" style="background:<?= $_accentColor ?>;display:none"><?= $initial ?></span>
                <?php else: ?><span class="ct4-avatar-initials" style="background:<?= $_accentColor ?>"><?= $initial ?></span><?php endif; ?>
              </div>
              <div class="ct4-compact-body">
                <p class="ct4-author-name"><?= e($_item['name'] ?? '') ?></p>
                <?php if ($metaParts): ?><p class="ct4-author-meta"><?= e(implode(' · ', $metaParts)) ?></p><?php endif; ?>
                <?php echo $starsHtml; ?>
                <?php if (!empty($_item['content'])): ?><p class="ct4-content"><?= e($_item['content']) ?></p><?php endif; ?>
              </div>
            </div>
          <?php else: ?>
            <div class="ct4-card">
              <?php if ($_showQuote): ?><span class="ct4-quote-icon">"</span><?php endif; ?>
              <div class="ct4-avatar-wrap">
                <?php if ($av): ?><img src="<?= $avIsUrl ? e($av) : asset('storage/'.$av) ?>" alt="<?= e($_item['name'] ?? '') ?>" referrerpolicy="no-referrer" loading="lazy" onerror="<?= $imgFallback ?>"><span class="ct4-avatar-initials" style="background:<?= $_accentColor ?>;display:none"><?= $initial ?></span>
                <?php else: ?><span class="ct4-avatar-initials" style="background:<?= $_accentColor ?>"><?= $initial ?></span><?php endif; ?>
              </div>
              <?php if ($_starPos === 'top') echo $starsHtml; ?>
              <div>
                <p class="ct4-author-name"><?= e($_item['name'] ?? '') ?></p>
                <?php if ($metaParts): ?><p class="ct4-author-meta"><?= e(implode(' · ', $metaParts)) ?></p><?php endif; ?>
              </div>
              <?php if ($_starPos === 'after_name') echo $starsHtml; ?>
              <div class="ct4-divider" style="background:<?= $_accentColor ?>"></div>
              <?php if ($_starPos === 'before_content' || $_starPos === '') echo $starsHtml; ?>
              <?php if (!empty($_item['content'])): ?><p class="ct4-content"><?= e($_item['content']) ?></p><?php endif; ?>
              <?php if ($_starPos === 'bottom') echo $starsHtml; ?>
            </div>
          <?php endif;
          return ob_get_clean();
        };
      @endphp

      @if($_layout === 'slider')
      <div class="ct4-slider-outer">
        <div class="ct4-slider-wrap" id="{{ $_uid }}-wrap">
          <div class="ct4-slider-track" id="{{ $_uid }}-track">
            @foreach($_items as $_item){!! $renderCard($_item) !!}@endforeach
          </div>
        </div>
        <button class="ct4-slider-nav prev" id="{{ $_uid }}-prev" aria-label="Trước">
          <svg viewBox="0 0 24 24"><polyline points="15,18 9,12 15,6"/></svg>
        </button>
        <button class="ct4-slider-nav next" id="{{ $_uid }}-next" aria-label="Sau">
          <svg viewBox="0 0 24 24"><polyline points="9,18 15,12 9,6"/></svg>
        </button>
      </div>
      @php $_pages = (int) ceil(count($_items) / max(1, $_cols)); @endphp
      @if($_pages > 1)
      <div class="ct4-dots" id="{{ $_uid }}-dots">
        @for($__p = 0; $__p < $_pages; $__p++)
        <button class="ct4-dot {{ $__p === 0 ? 'active' : '' }}"
          style="{{ $__p === 0 ? 'background:' . $_accentColor . ';width:24px;border-radius:4px' : '' }}"
          data-page="{{ $__p }}" aria-label="Trang {{ $__p + 1 }}"></button>
        @endfor
      </div>
      @endif
      <script>
      (function(){
        var uid    = '{{ $_uid }}';
        var cols   = {{ $_cols }};
        var accent = '{{ $_accentColor }}';
        var auto   = {{ $_autoplay ? 'true' : 'false' }};
        var speed  = {{ $_autoplaySpeed * 1000 }};
        var track  = document.getElementById(uid + '-track');
        var wrap   = document.getElementById(uid + '-wrap');
        var prev   = document.getElementById(uid + '-prev');
        var next   = document.getElementById(uid + '-next');
        var dotsEl = document.getElementById(uid + '-dots');
        if (!track) return;
        var cards = Array.from(track.querySelectorAll('.ct4-card'));
        var total = cards.length;
        var pages = Math.ceil(total / cols);
        var cur   = 0;
        var timer = null;
        function getOffset() {
          var gap = parseInt(getComputedStyle(track).gap) || 20;
          var cardW = cards[0] ? cards[0].offsetWidth : 0;
          return cur * cols * (cardW + gap);
        }
        function go(page) {
          cur = ((page % pages) + pages) % pages;
          track.style.transform = 'translateX(-' + getOffset() + 'px)';
          if (dotsEl) {
            dotsEl.querySelectorAll('.ct4-dot').forEach(function(d, i) {
              var a = i === cur;
              d.classList.toggle('active', a);
              d.style.background = a ? accent : '';
              d.style.width = a ? '24px' : '';
              d.style.borderRadius = a ? '4px' : '';
            });
          }
        }
        function startAuto() {
          if (!auto || pages < 2) return;
          stopAuto();
          timer = setInterval(function(){ go(cur + 1); }, speed);
        }
        function stopAuto() { if (timer) { clearInterval(timer); timer = null; } }
        if (prev) prev.addEventListener('click', function(){ go(cur - 1); startAuto(); });
        if (next) next.addEventListener('click', function(){ go(cur + 1); startAuto(); });
        if (dotsEl) dotsEl.querySelectorAll('.ct4-dot').forEach(function(d) {
          d.addEventListener('click', function(){ go(parseInt(this.dataset.page)); startAuto(); });
        });
        if (wrap) {
          wrap.addEventListener('mouseenter', stopAuto);
          wrap.addEventListener('mouseleave', startAuto);
        }
        window.addEventListener('resize', function(){ go(cur); });
        startAuto();
      })();
      </script>

      @elseif($_layout === 'featured')
        @php $_main = $_items[0] ?? null; $_rest = array_slice($_items, 1); @endphp
        <div class="ct4-featured-grid">
          @if($_main)
          <div class="ct4-featured-main">{!! $renderCard($_main) !!}</div>
          @endif
          @if(count($_rest) > 0)
          <div class="ct4-featured-list">
            @foreach($_rest as $_item){!! $renderCard($_item) !!}@endforeach
          </div>
          @endif
        </div>

      @elseif($_layout === 'centered')
        @php $_main = $_items[0] ?? null; @endphp
        @if($_main){!! $renderCard($_main) !!}@endif

      @else
      <div class="ct4-grid cols-{{ $_cols }}">
        @foreach($_items as $_item){!! $renderCard($_item) !!}@endforeach
      </div>
      @endif

    </div>
  </div>
</section>
@endif
