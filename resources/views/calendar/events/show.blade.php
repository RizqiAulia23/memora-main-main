<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $event->title }} - Memorify</title>
  <meta name="description" content="{{ $event->description }}" />
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

    @include('partials.dashboard-sidebar', ['activeSidebar' => 'calendar'])

    <main class="dash-main">

      @include('partials.dashboard-topbar')

      <div class="dash-content">

        @include('partials.flash-alerts')

        <!-- Page Header -->
        <section class="mem-head reveal" data-gsap-reveal>
          <div>
            <h1 class="mem-head-title">{{ $event->title }}</h1>
            <p class="mem-head-sub"><i class="fas fa-calendar-alt"></i> {{ $event->event_date->format('F j, Y') }} @if ($event->event_time) at {{ $event->event_time->format('H:i') }} @endif</p>
          </div>
          <a href="{{ route('calendar.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Calendar</a>
        </section>

        <section class="reveal reveal-delay-1" data-gsap-reveal>
          <div class="ev-detail">
            <div class="ev-detail-card">
              <div class="ev-detail-row">
                <i class="fas fa-heart"></i>
                <span>Added by <strong>{{ $event->user->name }}</strong> for <strong>{{ $event->partner->name }}</strong></span>
              </div>
              @if ($event->event_time)
                <div class="ev-detail-row">
                  <i class="fas fa-clock"></i>
                  <span>{{ $event->event_time->format('H:i') }}</span>
                </div>
              @endif
              @if ($event->location)
                <div class="ev-detail-row">
                  <i class="fas fa-map-marker-alt"></i>
                  <span>{{ $event->location }}</span>
                </div>
              @endif
              @if ($event->color)
                <div class="ev-detail-row">
                  <i class="fas fa-palette"></i>
                  <span class="ev-color-swatch" style="background: {{ $event->color }}"></span>
                </div>
              @endif
              @if ($event->description)
                <div class="ev-detail-row">
                  <i class="fas fa-align-left"></i>
                  <span>{{ $event->description }}</span>
                </div>
              @endif

              @if ($event->user_id === auth()->id())
                <div class="shm-form-actions" style="margin-top: 18px">
                  <a href="{{ route('events.edit', $event) }}" class="btn btn-outline btn-sm"><i class="fas fa-pen"></i> Edit</a>
                  <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('Delete this event?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm mem-btn-danger"><i class="fas fa-trash"></i> Delete</button>
                  </form>
                </div>
              @endif
            </div>
          </div>
        </section>

      </div>
    </main>

  </div>

  <div class="toast-container" id="toast-container" aria-live="polite"></div>

  @vite('resources/js/memorify-animations.js')
  @vite('resources/js/calendar-events-animations.js')
  <script src="{{ assetv('js/main.js') }}"></script>
  <script src="{{ assetv('js/dashboard.js') }}"></script>
</body>
</html>
