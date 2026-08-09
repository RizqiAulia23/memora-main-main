<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>Favorites - Memorify</title>
  <meta name="description" content="Your favorite memories." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="{{ asset('css/base.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/memories.css') }}">
  <link rel="stylesheet" href="{{ asset('css/favorites.css') }}">
</head>
<body>

  <div class="dash-layout">

    @include('partials.dashboard-sidebar', ['activeSidebar' => 'favorites'])

    <main class="dash-main">

      @include('partials.dashboard-topbar')

      <div class="dash-content">

        @include('partials.flash-alerts')

        <!-- Page Header -->
        <section class="mem-head reveal">
          <div>
            <h1 class="mem-head-title"><i class="fas fa-heart fav-head-icon"></i> Favorite Memories</h1>
            <p class="mem-head-sub">{{ $memories->total() }} {{ Str::plural('memory', $memories->total()) }} you hold closest to your heart.</p>
          </div>
          <a href="{{ route('memories.index') }}" class="btn btn-outline">
            <i class="fas fa-images"></i> All Memories
          </a>
        </section>

        <!-- Favorites Grid -->
        <section class="reveal reveal-delay-1" aria-label="Favorites list">
          @if ($memories->isNotEmpty())
            <div class="dash-memories-grid">
              @foreach ($memories as $memory)
                <article class="dash-memory-card" onclick="window.location='{{ route('memories.show', $memory) }}'">
                  <div class="dash-memory-img">
                    <img src="{{ $memory->imageUrl() }}" alt="{{ $memory->title }}" loading="lazy" />
                    <button type="button"
                            class="mem-card-fav active"
                            data-favorite-toggle
                            data-url="{{ route('favorites.toggle', $memory) }}"
                            aria-label="Remove from favorites"
                            onclick="event.stopPropagation()">
                      <i class="fas fa-heart"></i>
                    </button>
                    <span class="mem-card-actions">
                      <a href="{{ route('memories.show', $memory) }}" class="mem-card-btn" aria-label="View memory" onclick="event.stopPropagation()"><i class="fas fa-eye"></i></a>
                    </span>
                  </div>
                  <div class="dash-memory-info">
                    <a href="{{ route('memories.show', $memory) }}" class="dash-memory-title-link">
                      <div class="dash-memory-title">{{ $memory->title }}</div>
                    </a>
                    <div class="dash-memory-meta">
                      <span><i class="fas fa-calendar"></i> {{ $memory->memory_date->format('M j, Y') }}</span>
                    </div>
                  </div>
                </article>
              @endforeach
            </div>
          @else
            <div class="dash-section mem-empty">
              <div class="dash-empty">
                <div class="dash-empty-icon"><i class="fas fa-heart-broken"></i></div>
                <p>You have not favorited any memories yet. Tap the heart on any memory to keep it close.</p>
                <a href="{{ route('memories.index') }}" class="btn btn-primary btn-sm">Browse Memories</a>
              </div>
            </div>
          @endif

          @if ($memories->hasPages())
            <div class="mem-pagination-wrap">
              {{ $memories->links('vendor.pagination.memorify') }}
            </div>
          @endif
        </section>

      </div>
    </main>

  </div>

  <div class="toast-container" id="toast-container" aria-live="polite"></div>

  <script src="{{ asset('js/main.js') }}"></script>
  <script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>
