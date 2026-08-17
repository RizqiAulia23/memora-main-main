<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Search - Memorify</title>
  <meta name="description" content="Search your memories, letters, and photos." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="{{ assetv('css/base.css') }}">
  <link rel="stylesheet" href="{{ assetv('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ assetv('css/search.css') }}">
</head>
<body>

  <div class="dash-layout">

    @include('partials.dashboard-sidebar', ['activeSidebar' => 'search'])

    <main class="dash-main">

      @include('partials.dashboard-topbar')

      <div class="dash-content">

        @include('partials.flash-alerts')

        <!-- Page Header -->
        <section class="mem-head reveal" data-gsap-reveal>
          <div>
            <h1 class="mem-head-title">Search Everything</h1>
            <p class="mem-head-sub">Find any memory, photo, or letter in a heartbeat.</p>
          </div>
        </section>

        <!-- Big Search Box -->
        <section class="search-hero reveal reveal-delay-1" data-gsap-reveal>
          <form class="search-big" action="{{ route('search.index') }}" method="GET" role="search">
            <i class="fas fa-search"></i>
            <input type="text" name="q" value="{{ $query }}" placeholder="Try a date, a place, a feeling..." aria-label="Search all memories" autofocus />
            <button type="submit" class="btn btn-primary">Search</button>
          </form>
        </section>

        <!-- Results -->
        <section class="reveal reveal-delay-2" aria-label="Search results" data-gsap-reveal>
          @if ($query)
            @include('search._results', ['query' => $query, 'results' => $results])
          @else
            <div class="dash-section mem-empty">
              <div class="dash-empty">
                <div class="dash-empty-icon"><i class="fas fa-search"></i></div>
                <p>Type above to search across your memories, photos, and love letters.</p>
                <div class="dash-empty-actions">
                  <a href="{{ route('memories.index') }}" class="btn btn-outline btn-sm"><i class="fas fa-images"></i> Browse Memories</a>
                  <a href="{{ route('letters.index') }}" class="btn btn-outline btn-sm"><i class="fas fa-envelope-open-text"></i> View Letters</a>
                </div>
              </div>
            </div>
          @endif
        </section>

      </div>
    </main>

  </div>

  <div class="toast-container" id="toast-container" aria-live="polite"></div>

  @vite('resources/js/memorify-animations.js')
  @vite('resources/js/search-animations.js')
  <script src="{{ assetv('js/main.js') }}"></script>
  <script src="{{ assetv('js/dashboard.js') }}"></script>
</body>
</html>
