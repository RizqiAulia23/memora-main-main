<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Important Dates - Memorify</title>
  <meta name="description" content="Every special day, remembered." />
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

    @include('partials.dashboard-sidebar', ['activeSidebar' => 'important-dates'])

    <main class="dash-main">

      @include('partials.dashboard-topbar')

      <div class="dash-content">

        @include('partials.flash-alerts')

        <!-- Page Header -->
        <section class="mem-head reveal" data-gsap-reveal>
          <div>
            <h1 class="mem-head-title">Important Dates</h1>
            <p class="mem-head-sub">Every special day, remembered forever.</p>
          </div>
        </section>

        <!-- Add Date Form -->
        <section class="reveal reveal-delay-1" aria-label="Add an important date" data-gsap-reveal>
          <div class="id-form-wrap">
            <h3 class="cpl-feed-title"><i class="fas fa-calendar-plus"></i> Add a Date</h3>
            <form method="POST" action="{{ route('important-dates.store') }}" data-submit-feedback>
              @csrf

              <div class="id-form-grid">
                <div class="form-group">
                  <label for="title">Title</label>
                  <input type="text" id="title" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" maxlength="255" placeholder="e.g. Anniversary" required />
                  @error('title')
                    <span class="form-error">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="date">Date</label>
                  <input type="date" id="date" name="date" value="{{ old('date') }}" class="form-control @error('date') is-invalid @enderror" required />
                  @error('date')
                    <span class="form-error">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="id-form-grid">
                <div class="form-group">
                  <label for="type">Type</label>
                  <select id="type" name="type" class="form-control @error('type') is-invalid @enderror" required>
                    <option value="anniversary" @selected(old('type') === 'anniversary')>Anniversary</option>
                    <option value="birthday" @selected(old('type') === 'birthday')>Birthday</option>
                    <option value="first_meet" @selected(old('type') === 'first_meet')>First Meeting</option>
                    <option value="first_date" @selected(old('type') === 'first_date')>First Date</option>
                    <option value="custom" @selected(old('type') === 'custom' || old('type') === null)>Custom</option>
                  </select>
                  @error('type')
                    <span class="form-error">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="partner_id">Share with (optional)</label>
                  <select id="partner_id" name="partner_id" class="form-control @error('partner_id') is-invalid @enderror">
                    <option value="">Just me</option>
                    @foreach ($partners as $partner)
                      <option value="{{ $partner->id }}" @selected(old('partner_id') == $partner->id)>{{ $partner->name }}</option>
                    @endforeach
                  </select>
                  @error('partner_id')
                    <span class="form-error">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="form-group">
                <label for="description">Notes (optional)</label>
                <textarea id="description" name="description" rows="2" class="form-control @error('description') is-invalid @enderror" maxlength="1000" placeholder="What makes this day special?">{{ old('description') }}</textarea>
                @error('description')
                  <span class="form-error">{{ $message }}</span>
                @enderror
              </div>

              <label class="pl-check">
                <input type="checkbox" name="recurring" value="1" @checked(old('recurring')) />
                <span>Recurring every year (e.g. anniversaries, birthdays)</span>
              </label>

              <div class="shm-form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-heart"></i> Save Date</button>
              </div>
            </form>
          </div>
        </section>

        <!-- Dates Grid -->
        <section class="reveal reveal-delay-2" aria-label="Your important dates" data-gsap-reveal>
          <div class="cpl-feed-section">
            <div class="dash-section-header">
              <h3><i class="fas fa-calendar-heart"></i> Saved Dates</h3>
            </div>

            @if ($dates->isEmpty())
              <div class="dash-empty">
                <div class="dash-empty-icon"><i class="fas fa-calendar-heart"></i></div>
                <p>No important dates yet. Add your anniversary, birthdays, and the little moments that matter.</p>
              </div>
            @else
              <div class="id-grid">
                @foreach ($dates as $date)
                  @php $days = $date->daysUntil(); @endphp
                  <div class="id-card @if ($days === null) past @endif">
                    <div class="id-badge">
                      <i class="fas {{ match ($date->type) {
                          'anniversary' => 'fa-heart',
                          'birthday' => 'fa-cake-candles',
                          'first_meet' => 'fa-hand-holding-heart',
                          'first_date' => 'fa-martini-glass',
                          default => 'fa-star',
                      } }}"></i>
                      {{ ucwords(str_replace('_', ' ', $date->type)) }}
                    </div>
                    @if ($days === null)
                      <div class="id-past-label">Past date</div>
                    @elseif ($days === 0)
                      <div class="id-card-countdown">Today!</div>
                    @else
                      <div class="id-card-countdown">{{ $days }} days</div>
                    @endif
                    <div class="id-card-title">{{ $date->title }}</div>
                    <div class="id-card-meta">
                      <span class="id-badge"><i class="fas fa-calendar"></i> {{ $date->date->format('M j, Y') }}</span>
                      @if ($date->recurring)
                        <span class="id-badge recurring"><i class="fas fa-rotate"></i> Recurring</span>
                      @endif
                      @if ($date->partner_id)
                        <span class="id-badge"><i class="fas fa-share-nodes"></i> {{ $date->user_id === auth()->id() ? $date->partner->name : $date->user->name }}</span>
                      @endif
                    </div>
                    @if ($date->description)
                      <div class="id-card-desc">{{ $date->description }}</div>
                    @endif
                    @if ($date->user_id === auth()->id())
                      <div class="id-card-actions">
                        <a href="{{ route('important-dates.edit', $date) }}" class="pl-icon-btn" title="Edit"><i class="fas fa-pen"></i></a>
                        <form method="POST" action="{{ route('important-dates.destroy', $date) }}" onsubmit="return confirm('Delete this date?');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="pl-icon-btn pl-icon-btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                        </form>
                      </div>
                    @endif
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </section>

      </div>
    </main>

  </div>

  <div class="toast-container" id="toast-container" aria-live="polite"></div>

  @vite('resources/js/memorify-animations.js')
  @vite('resources/js/important-dates-animations.js')
  <script src="{{ assetv('js/main.js') }}"></script>
  <script src="{{ assetv('js/dashboard.js') }}"></script>
</body>
</html>
