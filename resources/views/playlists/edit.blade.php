<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rename Playlist - Memorify</title>
  <meta name="description" content="Rename your shared playlist." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="{{ assetv('css/base.css') }}">
  <link rel="stylesheet" href="{{ assetv('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ assetv('css/couple.css') }}">
</head>
<body>

  <div class="dash-layout">

    @include('partials.dashboard-sidebar', ['activeSidebar' => 'playlists'])

    <main class="dash-main">

      @include('partials.dashboard-topbar')

      <div class="dash-content">

        @include('partials.flash-alerts')

        <section class="mem-head reveal" data-gsap-reveal>
          <div>
            <h1 class="mem-head-title">Rename Playlist</h1>
            <p class="mem-head-sub">Give your playlist a name you both love.</p>
          </div>
        </section>

        <section class="reveal reveal-delay-1" data-gsap-reveal>
          <div class="pl-form-wrap">
            <form method="POST" action="{{ route('playlists.update', $playlist) }}" data-submit-feedback>
              @csrf
              @method('PUT')
              <div class="form-group">
                <label for="name">Playlist name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $playlist->name) }}" class="form-control @error('name') is-invalid @enderror" maxlength="80" required />
                @error('name')
                  <span class="form-error">{{ $message }}</span>
                @enderror
              </div>
              <div class="shm-form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Name</button>
                <a href="{{ route('playlists.index') }}" class="btn btn-outline">Cancel</a>
              </div>
            </form>
          </div>
        </section>

      </div>
    </main>

  </div>

  <div class="toast-container" id="toast-container" aria-live="polite"></div>

  @vite('resources/js/memorify-animations.js')
  @vite('resources/js/playlists-form-animations.js')
  <script src="{{ assetv('js/main.js') }}"></script>
  <script src="{{ assetv('js/dashboard.js') }}"></script>
</body>
</html>
