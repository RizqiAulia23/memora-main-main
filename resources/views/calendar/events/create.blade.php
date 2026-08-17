<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Event - Memorify</title>
  <meta name="description" content="Add a shared event." />
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
            <h1 class="mem-head-title">Add Shared Event</h1>
            <p class="mem-head-sub">Plan something beautiful together.</p>
          </div>
          <a href="{{ route('calendar.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Calendar</a>
        </section>

        <section class="reveal reveal-delay-1" data-gsap-reveal>
          @if ($partners->isNotEmpty())
            <form method="POST" action="{{ route('events.store') }}" class="ev-form-wrap" data-submit-feedback>
              @csrf

              <div class="form-group">
                <label for="partner_id">Share with</label>
                <select id="partner_id" name="partner_id" class="form-control @error('partner_id') is-invalid @enderror" required>
                  <option value="">Choose a partner...</option>
                  @foreach ($partners as $partner)
                    <option value="{{ $partner->id }}" {{ old('partner_id') == $partner->id ? 'selected' : '' }}>
                      {{ $partner->name }}
                    </option>
                  @endforeach
                </select>
                @error('partner_id')
                  <span class="form-error">{{ $message }}</span>
                @enderror
              </div>

              <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" maxlength="255" required />
                @error('title')
                  <span class="form-error">{{ $message }}</span>
                @enderror
              </div>

              <div class="ev-form-grid">
                <div class="form-group">
                  <label for="event_date">Date</label>
                  <input type="date" id="event_date" name="event_date" value="{{ old('event_date') }}" class="form-control @error('event_date') is-invalid @enderror" required />
                  @error('event_date')
                    <span class="form-error">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="event_time">Time (optional)</label>
                  <input type="time" id="event_time" name="event_time" value="{{ old('event_time') }}" class="form-control @error('event_time') is-invalid @enderror" />
                  @error('event_time')
                    <span class="form-error">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="ev-form-grid">
                <div class="form-group">
                  <label for="location">Location (optional)</label>
                  <input type="text" id="location" name="location" value="{{ old('location') }}" class="form-control @error('location') is-invalid @enderror" maxlength="255" />
                  @error('location')
                    <span class="form-error">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="color">Color (optional)</label>
                  <input type="text" id="color" name="color" value="{{ old('color') }}" class="form-control @error('color') is-invalid @enderror" maxlength="20" placeholder="#e8386a" />
                  @error('color')
                    <span class="form-error">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="form-group">
                <label for="description">Description (optional)</label>
                <textarea id="description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror" maxlength="5000">{{ old('description') }}</textarea>
                @error('description')
                  <span class="form-error">{{ $message }}</span>
                @enderror
              </div>

              <div class="shm-form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-calendar-plus"></i> Create Event</button>
                <a href="{{ route('calendar.index') }}" class="btn btn-outline">Cancel</a>
              </div>
            </form>
          @else
            <div class="ev-form-wrap">
              <div class="shm-no-partners">
                <div class="dash-empty-icon"><i class="fas fa-user-friends"></i></div>
                <p>You need a connected partner before you can share events.</p>
                <div class="dash-empty-actions">
                  <a href="{{ route('connections.index') }}" class="btn btn-primary btn-sm">Go to Connections</a>
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
  @vite('resources/js/calendar-events-animations.js')
  <script src="{{ assetv('js/main.js') }}"></script>
  <script src="{{ assetv('js/dashboard.js') }}"></script>
</body>
</html>
