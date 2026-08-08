<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gallery - Memorify</title>
  <meta name="description" content="Your photo gallery." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="{{ asset('css/base.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/gallery.css') }}">
</head>
<body>

  <div class="dash-layout">

    @include('partials.dashboard-sidebar', ['activeSidebar' => 'gallery'])

    <main class="dash-main">

      @include('partials.dashboard-topbar')

      <div class="dash-content">

        @if (session('success'))
          <div class="dash-alert dash-alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
          </div>
        @endif

        <!-- Page Header -->
        <section class="mem-head reveal">
          <div>
            <h1 class="mem-head-title">Photo Gallery</h1>
            <p class="mem-head-sub">Every snapshot tells a story worth keeping.</p>
          </div>
          <a href="{{ route('memories.create') }}" class="btn btn-primary">
            <i class="fas fa-cloud-upload-alt"></i> Upload Photos
          </a>
        </section>

        <!-- Grid -->
        <section class="reveal reveal-delay-1" aria-label="Photo gallery" data-gallery>
          @if ($photos->isNotEmpty())
            <div class="gal-grid" data-gallery-grid>
              @include('gallery._grid', ['photos' => $photos])
            </div>

            @if ($photos->hasMorePages())
              <div class="gal-load" data-gallery-load data-next="{{ $photos->nextPageUrl() }}">
                <div class="gal-loader">
                  <div class="gal-spinner"></div>
                  <span>Loading more memories...</span>
                </div>
              </div>
            @endif
          @else
            <div class="dash-section mem-empty">
              <div class="dash-empty">
                <div class="dash-empty-icon"><i class="fas fa-camera"></i></div>
                <p>Your gallery is empty. Add memories with photos to fill it with love.</p>
                <a href="{{ route('memories.create') }}" class="btn btn-primary btn-sm">Add a Photo</a>
              </div>
            </div>
          @endif
        </section>

      </div>
    </main>

  </div>

  <!-- Lightbox -->
  <div class="gal-lightbox" id="gal-lightbox" role="dialog" aria-modal="true" aria-label="Photo preview" hidden>
    <button class="gal-lightbox-close" data-lightbox-close aria-label="Close"><i class="fas fa-times"></i></button>
    <button class="gal-lightbox-nav gal-lightbox-prev" data-lightbox-prev aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
    <button class="gal-lightbox-nav gal-lightbox-next" data-lightbox-next aria-label="Next"><i class="fas fa-chevron-right"></i></button>
    <figure class="gal-lightbox-figure">
      <img src="" alt="" data-lightbox-img />
      <figcaption class="gal-lightbox-caption">
        <span data-lightbox-title></span>
        <span data-lightbox-date></span>
        <a href="#" data-lightbox-link class="btn btn-primary btn-sm">View Memory</a>
      </figcaption>
    </figure>
  </div>

  <div class="toast-container" id="toast-container" aria-live="polite"></div>

  <script src="{{ asset('js/main.js') }}"></script>
  <script src="{{ asset('js/dashboard.js') }}"></script>
  <script src="{{ asset('js/gallery.js') }}"></script>
</body>
</html>
