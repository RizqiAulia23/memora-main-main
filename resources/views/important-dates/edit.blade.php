<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Date - Memorify</title>
  <meta name="description" content="Edit an important date." />
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
            <h1 class="mem-head-title">Edit Date</h1>
            <p class="mem-head-sub">Keep the details just right.</p>
          </div>
          <a href="{{ route('important-dates.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Dates</a>
        </section>

        <section class="reveal reveal-delay-1" data-gsap-reveal>
          <div class="id-form-wrap">
            <form method="POST" action="{{ route('important-dates.update', $date) }}" data-submit-feedback>
              @csrf
              @method('PUT')

              <div class="ev-form-grid">
                <div class="form-group">
                  <label for="title">Title</label>
                  <input type="text" id="title" name="title" value="{{ old('title', $date->title) }}" class="form-control @error('title') is-invalid @enderror" maxlength="255" required />
                  @error('title')
                    <span class="form-error">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="date">Date</label>
                  <input type="date" id="date" name="date" value="{{ old('date', $date->date->format('Y-m-d')) }}" class="form-control @error('date') is-invalid @enderror" required />
                  @error('date')
                    <span class="form-error">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="form-group">
                <label for="type">Type</label>
                <select id="type" name="type" class="form-control @error('type') is-invalid @enderror" required>
                  <option value="anniversary" @selected(old('type', $date->type) === 'anniversary')>Anniversary</option>
                  <option value="birthday" @selected(old('type', $date->type) === 'birthday')>Birthday</option>
                  <option value="first_meet" @selected(old('type', $date->type) === 'first_meet')>First Meeting</option>
                  <option value="first_date" @selected(old('type', $date->type) === 'first_date')>First Date</option>
                  <option value="custom" @selected(old('type', $date->type) === 'custom')>Custom</option>
                </select>
                @error('type')
                  <span class="form-error">{{ $message }}</span>
                @enderror
              </div>

              <div class="form-group">
                <label for="description">Notes (optional)</label>
                <textarea id="description" name="description" rows="2" class="form-control @error('description') is-invalid @enderror" maxlength="1000">{{ old('description', $date->description) }}</textarea>
                @error('description')
                  <span class="form-error">{{ $message }}</span>
                @enderror
              </div>

              <label class="pl-check">
                <input type="checkbox" name="recurring" value="1" @checked(old('recurring', $date->recurring)) />
                <span>Recurring every year (e.g. anniversaries, birthdays)</span>
              </label>

              <div class="shm-form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                <a href="{{ route('important-dates.index') }}" class="btn btn-outline">Cancel</a>
              </div>
            </form>
          </div>
        </section>

      </div>
    </main>

  </div>

  <div class="toast-container" id="toast-container" aria-live="polite"></div>

  @vite('resources/js/memorify-animations.js')
  @vite('resources/js/important-dates-form-animations.js')
  <script src="{{ assetv('js/main.js') }}"></script>
  <script src="{{ assetv('js/dashboard.js') }}"></script>
</body>
</html>
