<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $loveLetter->title }} - Memorify</title>
  <meta name="description" content="A love letter from your memories." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="{{ asset('css/base.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/letters.css') }}">
</head>
<body>

  <div class="dash-layout">

    @include('partials.dashboard-sidebar', ['activeSidebar' => 'letters'])

    <main class="dash-main">

      @include('partials.dashboard-topbar')

      <div class="dash-content">

        @include('partials.flash-alerts')

        <!-- Letter Header -->
        <section class="letter-head reveal">
          <div class="letter-head-left">
            <div class="letter-mood-badge {{ $loveLetter->mood->value }}">
              <i class="{{ $loveLetter->mood->icon() }}"></i> {{ $loveLetter->mood->label() }}
            </div>
            <h1 class="letter-title">{{ $loveLetter->title }}</h1>
            <div class="letter-meta">
              <span><i class="fas fa-calendar"></i> {{ $loveLetter->letter_date->format('F j, Y') }}</span>
              @if ($loveLetter->is_pinned)
                <span class="letter-pin-badge"><i class="fas fa-thumbtack"></i> Pinned</span>
              @endif
            </div>
          </div>
          <div class="letter-head-actions">
            <form method="POST" action="{{ route('letters.toggle-pin', $loveLetter) }}" class="letter-pin-form">
              @csrf
              <button type="submit" class="btn btn-outline btn-sm {{ $loveLetter->is_pinned ? 'active' : '' }}">
                <i class="fas fa-thumbtack"></i> {{ $loveLetter->is_pinned ? 'Unpin' : 'Pin' }}
              </button>
            </form>
            <a href="{{ route('letters.edit', $loveLetter) }}" class="btn btn-outline btn-sm"><i class="fas fa-pen"></i> Edit</a>
            <form method="POST" action="{{ route('letters.destroy', $loveLetter) }}" onsubmit="return confirm('Delete this love letter?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button>
            </form>
          </div>
        </section>

        <!-- Letter Paper -->
        <section class="letter-paper reveal reveal-delay-1" aria-label="Letter content">
          <div class="letter-paper-top">
            <span>{{ auth()->user()->name }}</span>
            <i class="fas fa-heart letter-paper-heart"></i>
            <span>{{ $loveLetter->letter_date->format('M j, Y') }}</span>
          </div>
          <div class="letter-paper-content">
            {!! $loveLetter->content !!}
          </div>
          <div class="letter-paper-sign">
            With all my love,<br />
            <strong>{{ auth()->user()->name }}</strong>
          </div>
        </section>

        <div class="letter-back">
          <a href="{{ route('letters.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Letters</a>
        </div>

      </div>
    </main>

  </div>

  <div class="toast-container" id="toast-container" aria-live="polite"></div>

  <script src="{{ asset('js/main.js') }}"></script>
  <script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>
