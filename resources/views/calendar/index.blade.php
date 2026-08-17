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
  <link rel="stylesheet" href="{{ assetv('css/base.css') }}">
  <link rel="stylesheet" href="{{ assetv('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ assetv('css/calendar.css') }}">
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
            <h1 class="mem-head-title">Memory Calendar</h1>
            <p class="mem-head-sub">Relive each day, month by month.</p>
          </div>
          <div class="mem-head-actions">
            <a href="{{ route('memories.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Memory</a>
            <a href="{{ route('events.create') }}" class="btn btn-outline"><i class="fas fa-calendar-plus"></i> Add Event</a>
          </div>
        </section>

        <section class="cal-wrap reveal reveal-delay-1" data-gsap-reveal>
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
                        class="cal-day {{ $cell['isToday'] ? 'today' : '' }} {{ $cell['hasMemory'] ? 'has-memory' : '' }} {{ $eventDates->has($cell['date']) ? 'has-event' : '' }} {{ $cell['otherMonth'] ? 'other-month' : '' }}"
                        data-cal-day
                        data-date="{{ $cell['date'] }}"
                        @disabled(! $cell['hasMemory'] && ! $eventDates->has($cell['date']))>
                  {{ $cell['day'] }}
                  @if ($cell['hasMemory'])
                    <i class="fas fa-heart cal-day-heart"></i>
                  @endif
                  @if ($eventDates->has($cell['date']))
                    <span class="cal-event-dot" aria-hidden="true"></span>
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
          <div class="dash-section mem-empty reveal reveal-delay-2" data-gsap-reveal>
            <div class="dash-empty">
              <div class="dash-empty-icon"><i class="fas fa-calendar-plus"></i></div>
              <p>Your calendar is still quiet. Add your first memory and this month will light up with hearts.</p>
              <a href="{{ route('memories.create') }}" class="btn btn-primary btn-sm">Add Your First Memory</a>
            </div>
          </div>
        @endif

        <!-- Shared Events -->
        @if ($events->isNotEmpty())
          <section class="reveal reveal-delay-2" aria-label="Shared events" data-gsap-reveal>
            <div class="cpl-feed-section">
              <div class="dash-section-header">
                <h3><i class="fas fa-calendar-alt"></i> Events in {{ $month->format('F Y') }}</h3>
                <a href="{{ route('events.create') }}">Add Event <i class="fas fa-arrow-right"></i></a>
              </div>
              <div class="ev-list">
                @foreach ($events as $event)
                  <div class="ev-item">
                    <div class="ev-item-dot">
                      <i class="fas {{ $event->event_time ? 'fa-clock' : 'fa-calendar-day' }}"></i>
                    </div>
                    <a href="{{ route('events.show', $event) }}" class="ev-item-body">
                      <div class="ev-item-title">{{ $event->title }}</div>
                      <div class="ev-item-meta">
                        <span><i class="fas fa-calendar"></i> {{ $event->event_date->format('M j, Y') }}</span>
                        @if ($event->event_time)
                          <span><i class="fas fa-clock"></i> {{ $event->event_time->format('H:i') }}</span>
                        @endif
                        @if ($event->location)
                          <span><i class="fas fa-map-marker-alt"></i> {{ $event->location }}</span>
                        @endif
                      </div>
                      <div class="ev-item-owner">
                        <i class="fas fa-user"></i> {{ $event->user_id === auth()->id() ? 'You' : $event->user->name }}
                      </div>
                    </a>
                    @if ($event->user_id === auth()->id())
                      <div class="ev-item-actions">
                        <a href="{{ route('events.edit', $event) }}" class="pl-icon-btn" title="Edit"><i class="fas fa-pen"></i></a>
                        <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('Delete this event?');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="pl-icon-btn pl-icon-btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                        </form>
                      </div>
                    @endif
                  </div>
                @endforeach
              </div>
            </div>
          </section>
        @endif

      </div>
    </main>

  </div>

  <div class="toast-container" id="toast-container" aria-live="polite"></div>

  @vite('resources/js/memorify-animations.js')
  @vite('resources/js/calendar-animations.js')
  <script src="{{ assetv('js/main.js') }}"></script>
  <script src="{{ assetv('js/dashboard.js') }}"></script>
  <script src="{{ assetv('js/calendar.js') }}"></script>
</body>
</html>
