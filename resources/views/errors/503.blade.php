<!doctype html>
<html lang="en" data-theme="{{ $theme ?? auth()->user()?->settings?->theme ?? 'light' }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Maintenance - Memorify</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="{{ assetv('css/base.css') }}" />
  <style>
    .err-cover { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 24px;
      background: linear-gradient(160deg, #fff5f7 0%, #ffffff 45%, #ffe9ea 100%); }
    .err-card { max-width: 520px; width: 100%; text-align: center; }
    .err-code { font-family: 'Playfair Display', serif; font-weight: 800; font-size: clamp(72px, 16vw, 128px);
      line-height: 1; background: linear-gradient(135deg, var(--pink-500), var(--purple-500));
      -webkit-background-clip: text; background-clip: text; color: transparent; }
    .err-icon { width: 64px; height: 64px; margin: 0 auto 20px; border-radius: 50%; display: flex; align-items: center;
      justify-content: center; background: var(--pink-100); color: var(--pink-600); font-size: 24px; }
    .err-title { font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 700; color: var(--ink-900); margin-bottom: 10px; }
    .err-text { color: var(--ink-600); font-size: 15px; line-height: 1.7; margin-bottom: 28px; }
    .err-nav { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
    [data-theme="dark"] .err-cover { background: linear-gradient(160deg, #17141c 0%, #1e1a24 45%, #241a20 100%); }
    [data-theme="dark"] .err-title { color: var(--dt-text); }
    [data-theme="dark"] .err-text { color: var(--dt-text-muted); }
    [data-theme="dark"] .err-icon { background: var(--dt-pink-deep); color: var(--pink-400); }
  </style>
</head>
<body>
  <div class="err-cover">
    <div class="err-card">
      <div class="err-icon"><i class="fas fa-wrench"></i></div>
      <div class="err-code">503</div>
      <h1 class="err-title">Tiny maintenance break</h1>
      <p class="err-text">We're doing some love work behind the scenes. Your memories are safe — check back shortly.</p>
      <div class="err-nav">
        <a href="javascript:location.reload()" class="btn btn-primary">Retry Now</a>
        <a href="{{ route('home') }}" class="btn btn-outline">Back to Home</a>
      </div>
    </div>
  </div>
</body>
</html>