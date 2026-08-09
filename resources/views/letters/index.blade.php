<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Love Letters - Memorify</title>
  <meta name="description" content="Write and read your love letters." />
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

        @if (session('success'))
          <div class="dash-alert dash-alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
          </div>
        @endif

        <!-- Page Header -->
        <section class="mem-head reveal">
          <div>
            <h1 class="mem-head-title">Love Letters</h1>
            <p class="mem-head-sub">Words that never fade.</p>
          </div>
          <a href="{{ route('letters.create') }}" class="btn btn-primary">
            <i class="fas fa-feather-alt"></i> Write a Letter
          </a>
        </section>

        <!-- Letters -->
        <section class="reveal reveal-delay-1" aria-label="Love letters list">
          @if ($letters->isNotEmpty())
            <div class="letter-list">
              @foreach ($letters as $letter)
                <article class="letter-card {{ $letter->is_pinned ? 'pinned' : '' }} reveal">
                  @if ($letter->is_pinned)
                    <span class="letter-pin-badge-top"><i class="fas fa-thumbtack"></i> Pinned</span>
                  @endif
                  <div class="letter-card-mood {{ $letter->mood->value }}" title="{{ $letter->mood->label() }}">
                    <i class="{{ $letter->mood->icon() }}"></i>
                  </div>
                  <div class="letter-card-body">
                    <div class="letter-card-top">
                      <h3><a href="{{ route('letters.show', $letter) }}">{{ $letter->title }}</a></h3>
                      <form method="POST" action="{{ route('letters.toggle-pin', $letter) }}" class="letter-pin-form">
                        @csrf
                        <button type="submit" class="letter-pin {{ $letter->is_pinned ? 'active' : '' }}" aria-label="{{ $letter->is_pinned ? 'Unpin letter' : 'Pin letter' }}" title="{{ $letter->is_pinned ? 'Unpin' : 'Pin to top' }}">
                          <i class="fas {{ $letter->is_pinned ? 'fa-thumbtack' : 'fa-thumbtack fa-rotate-90' }}"></i>
                        </button>
                      </form>
                    </div>
                    <p class="letter-card-excerpt">{{ Str::limit(strip_tags($letter->content), 140) }}</p>
                    <div class="letter-card-meta">
                      <span><i class="fas fa-calendar"></i> {{ $letter->letter_date->format('M j, Y') }}</span>
                      <span><i class="fas fa-smile"></i> {{ $letter->mood->label() }}</span>
                    </div>
                  </div>
                  <div class="letter-card-actions">
                    <a href="{{ route('letters.show', $letter) }}" class="letter-btn" title="Read"><i class="fas fa-book-open"></i></a>
                    <a href="{{ route('letters.edit', $letter) }}" class="letter-btn" title="Edit"><i class="fas fa-pen"></i></a>
                    <form method="POST" action="{{ route('letters.destroy', $letter) }}" onsubmit="return confirm('Delete this love letter?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="letter-btn letter-btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                    </form>
                  </div>
                </article>
              @endforeach
            </div>
          @else
            <div class="dash-section mem-empty">
              <div class="dash-empty">
                <div class="dash-empty-icon"><i class="fas fa-envelope-open-text"></i></div>
                <p>No love letters yet. Pour your heart onto the page.</p>
                <a href="{{ route('letters.create') }}" class="btn btn-primary btn-sm">Write Your First Letter</a>
              </div>
            </div>
          @endif

          @if ($letters->hasPages())
            <div class="mem-pagination-wrap">
              {{ $letters->links('vendor.pagination.memorify') }}
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
