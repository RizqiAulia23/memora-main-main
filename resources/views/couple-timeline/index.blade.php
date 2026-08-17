<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Couple Timeline - Memorify</title>
  <meta name="description" content="Every moment of your story, together." />
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

    @include('partials.dashboard-sidebar', ['activeSidebar' => 'couple-timeline'])

    <main class="dash-main">

      @include('partials.dashboard-topbar')

      <div class="dash-content">

        @include('partials.flash-alerts')

        <!-- Page Header -->
        <section class="mem-head reveal" data-gsap-reveal>
          <div>
            <h1 class="mem-head-title">Couple Timeline</h1>
            <p class="mem-head-sub">Connections, shared memories, letters, and the dates that matter.</p>
          </div>
        </section>

        <!-- Feed -->
        <section class="reveal reveal-delay-1" aria-label="Couple timeline feed" data-gsap-reveal>
          @if ($feed->isEmpty())
            <div class="dash-section mem-empty">
              <div class="dash-empty">
                <div class="dash-empty-icon"><i class="fas fa-heart"></i></div>
                <p>Connect with your partner to start your couple timeline. Shared memories, letters, events and important dates will appear here.</p>
                <a href="{{ route('connections.index') }}" class="btn btn-primary btn-sm">Find Your Partner</a>
              </div>
            </div>
          @else
            <div class="cpl-feed">
              @foreach ($feed as $item)
                <div class="cpl-feed-item">
                  <div class="cpl-feed-icon">
                    <i class="fas {{ $item['icon'] }}"></i>
                  </div>
                  <div class="cpl-feed-body">
                    <div class="cpl-feed-title">{{ $item['title'] }}</div>
                    <div class="cpl-feed-meta">{{ $item['subtitle'] }} &middot; {{ $item['created_at']->diffForHumans() }}</div>
                  </div>
                </div>
              @endforeach
            </div>

            @if ($feed->hasPages())
              <div class="mem-pagination-wrap reveal reveal-delay-2" data-gsap-reveal>
                {{ $feed->links('vendor.pagination.memorify') }}
              </div>
            @endif
          @endif
        </section>

      </div>
    </main>

  </div>

  <div class="toast-container" id="toast-container" aria-live="polite"></div>

  @vite('resources/js/memorify-animations.js')
  @vite('resources/js/couple-timeline-animations.js')
  <script src="{{ assetv('js/main.js') }}"></script>
  <script src="{{ assetv('js/dashboard.js') }}"></script>
</body>
</html>
