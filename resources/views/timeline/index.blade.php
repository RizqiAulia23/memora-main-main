<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Timeline - Memorify</title>
  <meta name="description" content="Your memories timeline." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="{{ asset('css/base.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/timeline.css') }}">
</head>
<body>

  <div class="dash-layout">

    @include('partials.dashboard-sidebar', ['activeSidebar' => 'timeline'])

    <main class="dash-main">

      @include('partials.dashboard-topbar')

      <div class="dash-content">

        <!-- Page Header -->
        <section class="mem-head reveal">
          <div>
            <h1 class="mem-head-title">Our Timeline</h1>
            <p class="mem-head-sub">Scroll through the story of us, year by year.</p>
          </div>
          <a href="{{ route('memories.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Memory</a>
        </section>

        <!-- Year Pills -->
        @if ($years->isNotEmpty())
          <section class="timeline-years reveal reveal-delay-1" aria-label="Year navigation">
            @foreach ($years as $year)
              <a href="{{ route('timeline.index', ['year' => $year]) }}" class="timeline-year {{ $year === $selectedYear ? 'active' : '' }}">{{ $year }}</a>
            @endforeach
          </section>

          <!-- Timeline -->
          <section class="timeline reveal reveal-delay-2" aria-label="Memories timeline">
            @foreach ($grouped as $monthName => $monthMemories)
              <div class="timeline-month">
                <div class="timeline-month-label">
                  <i class="fas fa-heart"></i> {{ $monthName }} {{ $selectedYear }}
                </div>
                <div class="timeline-month-list">
                  @foreach ($monthMemories as $memory)
                    <div class="timeline-item">
                      <div class="timeline-item-dot"></div>
                      <a href="{{ route('memories.show', $memory) }}" class="timeline-item-card">
                        @if ($memory->image)
                          <img src="{{ $memory->imageUrl() }}" alt="{{ $memory->title }}" loading="lazy" />
                        @endif
                        <div class="timeline-item-info">
                          <div class="timeline-item-date">{{ $memory->memory_date->format('F j, Y') }}</div>
                          <div class="timeline-item-title">{{ $memory->title }}</div>
                        </div>
                      </a>
                    </div>
                  @endforeach
                </div>
              </div>
            @endforeach
          </section>
        @else
          <div class="dash-section mem-empty reveal">
            <div class="dash-empty">
              <div class="dash-empty-icon"><i class="fas fa-stream"></i></div>
              <p>Your timeline will come to life once you add memories.</p>
              <a href="{{ route('memories.create') }}" class="btn btn-primary btn-sm">Add Your First Memory</a>
            </div>
          </div>
        @endif

      </div>
    </main>

  </div>

  <div class="toast-container" id="toast-container" aria-live="polite"></div>

  <script src="{{ asset('js/main.js') }}"></script>
  <script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>
