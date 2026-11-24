<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bảo trì website - {{ \App\Models\Setting::get('site_name','Website') }}</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:'Outfit',sans-serif;background:#0f172a;color:#f8fafc;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
    .wrap{max-width:560px;width:100%;text-align:center}
    .icon{width:80px;height:80px;margin:0 auto 28px;background:rgba(249,115,22,.15);border-radius:50%;display:flex;align-items:center;justify-content:center}
    .icon svg{width:40px;height:40px;color:#f97316}
    h1{font-size:28px;font-weight:700;margin-bottom:12px}
    .message{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:12px;padding:24px 28px;text-align:center;line-height:1.8;font-size:15px;color:#cbd5e1}
    .message p{margin-bottom:8px;text-align:center !important}
    .message p:last-child{margin-bottom:0}
    .footer{margin-top:32px;font-size:13px;color:#475569}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
      </svg>
    </div>
    <h1>Website đang bảo trì</h1>
    <div class="message rich-content">
      {!! $message !!}
    </div>
    <div class="footer">© {{ date('Y') }} {{ \App\Models\Setting::get('site_name','Website') }}</div>
  </div>
</body>
</html>
