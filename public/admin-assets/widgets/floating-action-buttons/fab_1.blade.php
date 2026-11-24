@php
  $_fabPhone    = $hotlineTel ?? preg_replace('/\s+/', '', \App\Models\Setting::get('hotline',''));
  $_fabLabel    = $hotline    ?? \App\Models\Setting::get('hotline','');
  $_fabZalo     = $zalo       ?? \App\Models\Setting::get('zalo','');
  $_fabFacebook = $facebook   ?? \App\Models\Setting::get('facebook_url','#');
  $_fabSettings = $block->settings ?? [];
  $_fabSide     = ($block->zone ?? '') === 'float_left' ? 'left:20px' : 'right:20px';
  $_fabPos      = match($_fabSettings['position'] ?? 'middle') {
    'top'    => 'top:80px',
    'bottom' => 'bottom:80px',
    default  => 'top:50%;transform:translateY(-50%)',
  };
@endphp

<div class="fab1" aria-label="Liên hệ nhanh" style="position:fixed;{{ $_fabSide }};{{ $_fabPos }};z-index:9990;display:flex;flex-direction:column;align-items:flex-end;gap:10px">
  <style>
    .fab1-btn{position:relative;width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;text-decoration:none;border:none;cursor:pointer;box-shadow:0 4px 14px rgba(0,0,0,.22);transition:transform .2s,box-shadow .2s;flex-shrink:0}
    .fab1-btn:hover{transform:scale(1.1);box-shadow:0 6px 20px rgba(0,0,0,.3)}
    .fab1-btn svg{width:22px;height:22px;flex-shrink:0}
    .fab1-phone{background:#e53935;color:#fff}
    .fab1-phone::before,.fab1-phone::after{content:'';position:absolute;inset:0;border-radius:50%;background:#e53935;opacity:.5;animation:fab1-ring 2s ease-out infinite}
    .fab1-phone::after{animation-delay:.6s}
    @keyframes fab1-ring{0%{transform:scale(1);opacity:.5}100%{transform:scale(2.2);opacity:0}}
    .fab1-tip{position:absolute;right:56px;background:rgba(0,0,0,.75);color:#fff;font-size:12px;white-space:nowrap;padding:4px 10px;border-radius:6px;pointer-events:none;opacity:0;transition:opacity .15s;font-family:inherit}
    .fab1-btn:hover .fab1-tip{opacity:1}
    .fab1-zalo{background:#0068ff;color:#fff}
    .fab1-fb{background:#1877f2;color:#fff}
    .fab1-top{background:#fff;color:#0B1B3A;border:1px solid #e1e8f5;opacity:0;pointer-events:none;transition:opacity .25s,transform .2s,box-shadow .2s}
    .fab1-top.visible{opacity:1;pointer-events:auto}
    .fab1-top:hover{transform:scale(1.1);box-shadow:0 6px 20px rgba(0,0,0,.3)}
  </style>

  @if($_fabPhone)
  <a href="tel:{{ $_fabPhone }}" class="fab1-btn fab1-phone" aria-label="Gọi {{ $_fabLabel }}">
    <span class="fab1-tip">{{ $_fabLabel ?: $_fabPhone }}</span>
    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1L6.6 10.8z"/></svg>
  </a>
  @endif

  @if($_fabZalo)
  <a href="https://zalo.me/{{ $_fabZalo }}" target="_blank" rel="noopener" class="fab1-btn fab1-zalo" aria-label="Zalo">
    <span class="fab1-tip">Chat Zalo</span>
    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.5 2 2 5.9 2 10.7c0 2.7 1.5 5.1 3.8 6.7-.1.7-.5 2-.5 2.3 0 .3.2.5.5.4.4-.2 2.4-1.5 3-1.9 1 .2 2.1.4 3.2.4 5.5 0 10-3.9 10-8.7C22 5.9 17.5 2 12 2z"/></svg>
  </a>
  @endif

  @if($_fabFacebook && $_fabFacebook !== '#')
  <a href="{{ $_fabFacebook }}" target="_blank" rel="noopener" class="fab1-btn fab1-fb" aria-label="Facebook">
    <span class="fab1-tip">Facebook</span>
    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 12a10 10 0 10-11.6 9.9v-7H8v-2.9h2.4V9.7c0-2.4 1.4-3.8 3.6-3.8 1 0 2.1.2 2.1.2v2.3h-1.2c-1.2 0-1.5.7-1.5 1.5V12H16l-.4 2.9h-2.2v7A10 10 0 0022 12z"/></svg>
  </a>
  @endif

  <button class="fab1-btn fab1-top" id="fab1Top" aria-label="Lên đầu trang">
    <span class="fab1-tip">Lên đầu</span>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
  </button>
</div>

<script>
(function(){
  var btn = document.getElementById('fab1Top');
  if(!btn) return;
  window.addEventListener('scroll', function(){
    btn.classList.toggle('visible', window.scrollY > 300);
  }, {passive:true});
  btn.addEventListener('click', function(){
    window.scrollTo({top:0, behavior:'smooth'});
  });
})();
</script>
