<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Calendar - Memorify</title>
  <meta name="description" content="Your memories calendar." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="{{ asset('css/base.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/calendar.css') }}">
</head>
<body>

  <div class="dash-layout">

    @include('partials.dashboard-sidebar', ['activeSidebar' => 'calendar'])

    <main class="dash-main">

      @include('partials.dashboard-topbar')

      <div class="dash-content">

        <!-- Page Header -->
        <section class="mem-head reveal">
          <div>
            <h1 class="mem-head-title">Memory Calendar</h1>
            <p class="mem-head-sub">Relive each day, month by month.</p>
          </div>
          <a href="{{ route('memories.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Memory</a>
        </section>

        <section class="cal-wrap reveal reveal-delay-1">
          <div class="cal">
            <div class="cal-header">
              <a href="{{ route('calendar.index', ['month' => $prevMonth]) }}" class="cal-nav" aria-label="Previous month"><i class="fas fa-chevron-left"></i></a>
              <h2>{{ $month->format('F Y') }}</h2>
              <a href="{{ route('calendar.index', ['month' => $nextMonth]) }}" class="cal-nav" aria-label="Next month"><i class="fas fa-chevron-right"></i></a>
            </div>

            <div class="cal-grid">
              <div class="cal-day-name">Sun</div>
              <div class="cal-day-name">Mon</div>
              <div class="cal-day-name">Tue</div>
              <div class="cal-day-name">Wed</div>
              <div class="cal-day-name">Thu</div>
              <div class="cal-day-name">Fri</div>
              <div class="cal-day-name">Sat</div>

              @foreach ($days as $cell)
                <button type="button"
                        class="cal-day {{ $cell['isToday'] ? 'today' : '' }} {{ $cell['hasMemory'] ? 'has-memory' : '' }} {{ $cell['otherMonth'] ? 'other-month' : '' }}"
                        data-cal-day
                        data-date="{{ $cell['date'] }}"
                        @disabled(! $cell['hasMemory'])>
                  {{ $cell['day'] }}
                  @if ($cell['hasMemory'])
                    <i class="fas fa-heart cal-day-heart"></i>
                  @endif
                </button>
              @endforeach
            </div>
          </div>

          <!-- Day details panel -->
          <aside class="cal-details" data-endpoint="{{ route('calendar.date') }}" aria-live="polite">
            <div class="cal-details-empty">
              <i class="fas fa-heart"></i>
              <p>Select a day marked with a heart to relive its memories.</p>
            </div>
          </aside>
        </section>

        @if (! $hasMemories)
          <div class="dash-section mem-empty reveal reveal-delay-2">
            <div class="dash-empty">
              <div class="dash-empty-icon"><i class="fas fa-calendar-plus"></i></div>
              <p>Your calendar is still quiet. Add your first memory and this month will light up with hearts.</p>
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
  <script src="{{ asset('js/calendar.js') }}"></script>
</body>
</html>
