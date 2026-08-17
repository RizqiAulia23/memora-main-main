<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bucket List - Memorify</title>
  <meta name="description" content="Dreams we chase together." />
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

    @include('partials.dashboard-sidebar', ['activeSidebar' => 'bucket-list'])

    <main class="dash-main">

      @include('partials.dashboard-topbar')

      <div class="dash-content">

        @include('partials.flash-alerts')

        <!-- Page Header -->
        <section class="mem-head reveal" data-gsap-reveal>
          <div>
            <h1 class="mem-head-title"><i class="fas fa-list-check bl-head-icon"></i> Bucket List</h1>
            <p class="mem-head-sub">Dreams we chase together.</p>
          </div>
        </section>

        @if ($partners->isEmpty())
          <section class="reveal reveal-delay-1" aria-label="Add a bucket list item" data-gsap-reveal>
            <div class="dash-empty">
              <div class="dash-empty-icon"><i class="fas fa-list-check"></i></div>
              <p>Connect with your partner first, then dream big together.</p>
            </div>
          </section>
        @else
          <!-- Add Item -->
          <section class="reveal reveal-delay-1" aria-label="Add a bucket list item" data-gsap-reveal>
            <div class="pl-form-wrap">
              <h3 class="cpl-feed-title"><i class="fas fa-plus-circle"></i> Add an Adventure</h3>
              <form method="POST" action="{{ route('bucket-list.store') }}" data-submit-feedback>
                @csrf
                <div class="pl-add-form">
                  <div class="form-group">
                    <label for="partner_id">Partner</label>
                    <select id="partner_id" name="partner_id" class="form-control @error('partner_id') is-invalid @enderror" required>
                      @foreach ($partners as $partner)
                        <option value="{{ $partner->id }}" @selected(old('partner_id') == $partner->id)>{{ $partner->name }}</option>
                      @endforeach
                    </select>
                    @error('partner_id')
                      <span class="form-error">{{ $message }}</span>
                    @enderror
                  </div>
                  <div class="form-group">
                    <label for="title">What do we want to do?</label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" maxlength="120" placeholder="e.g. Watch the sunrise from a mountain" required />
                    @error('title')
                      <span class="form-error">{{ $message }}</span>
                    @enderror
                  </div>
                  <div class="form-group full">
                    <label for="description">Notes (optional)</label>
                    <textarea id="description" name="description" rows="2" class="form-control @error('description') is-invalid @enderror" maxlength="1000" placeholder="When, where, why?">{{ old('description') }}</textarea>
                    @error('description')
                      <span class="form-error">{{ $message }}</span>
                    @enderror
                  </div>
                </div>
                <div class="shm-form-actions">
                  <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add to Bucket List</button>
                </div>
              </form>
            </div>
          </section>
        @endif

        <!-- Progress -->
        <section class="reveal reveal-delay-2" aria-label="Bucket list progress" data-gsap-reveal>
          @php
            $total = $items->count();
            $done = $items->where('status', 'completed')->count();
            $pct = $total > 0 ? (int) round(($done / $total) * 100) : 0;
          @endphp
          <div class="bl-progress-card">
            <div class="bl-progress-top">
              <span>Adventures completed</span>
              <span class="bl-progress-num">{{ $done }}<span> / {{ $total }}</span></span>
            </div>
            <div class="bl-progress-bar">
              <div class="bl-progress-fill" style="width: {{ $pct }}%"></div>
            </div>
          </div>

          <!-- Filters -->
          <div class="bl-filters" role="group" aria-label="Filter bucket list">
            <a href="{{ route('bucket-list.index') }}" class="bl-filter-pill {{ ! request()->filled('status') ? 'active' : '' }}">All</a>
            <a href="{{ route('bucket-list.index', ['status' => 'planned']) }}" class="bl-filter-pill {{ request('status') === 'planned' ? 'active' : '' }}">Planned</a>
            <a href="{{ route('bucket-list.index', ['status' => 'completed']) }}" class="bl-filter-pill {{ request('status') === 'completed' ? 'active' : '' }}">Completed</a>
          </div>

          @if ($items->isEmpty())
            <div class="dash-empty">
              <div class="dash-empty-icon"><i class="fas fa-mountain-sun"></i></div>
              <p>No bucket list items yet. Add your first shared adventure above.</p>
            </div>
          @else
            <div class="bl-list">
              @foreach ($items as $item)
                <div class="bl-item @if ($item->isCompleted()) completed @endif">
                  <form method="POST" action="{{ route('bucket-list.toggle', $item) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="bl-check" aria-label="{{ $item->isCompleted() ? 'Mark as planned' : 'Mark as completed' }}">
                      <i class="fas {{ $item->isCompleted() ? 'fa-check-circle' : 'fa-circle' }}"></i>
                    </button>
                  </form>
                  <div class="bl-item-body">
                    <div class="bl-item-title">{{ $item->title }}</div>
                    @if ($item->description)
                      <div class="bl-item-desc">{{ $item->description }}</div>
                    @endif
                    <div class="bl-item-meta">
                      <span><i class="fas fa-user-plus"></i> {{ $item->user_id === auth()->id() ? 'You' : $item->user->name }}</span>
                      @if ($item->isCompleted() && $item->completed_at)
                        <span><i class="fas fa-check-double"></i> {{ $item->completed_at->format('M j, Y') }}</span>
                      @endif
                    </div>
                  </div>
                  @if ($item->user_id === auth()->id())
                    <div class="bl-item-actions">
                      <form method="POST" action="{{ route('bucket-list.destroy', $item) }}" onsubmit="return confirm('Remove this bucket list item?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="pl-icon-btn pl-icon-btn-danger" title="Remove" aria-label="Remove {{ $item->title }}"><i class="fas fa-trash"></i></button>
                      </form>
                    </div>
                  @endif
                </div>
              @endforeach
            </div>
          @endif
        </section>

      </div>
    </main>

  </div>

  <div class="toast-container" id="toast-container" aria-live="polite"></div>

  @vite('resources/js/memorify-animations.js')
  @vite('resources/js/bucket-list-animations.js')
  <script src="{{ assetv('js/main.js') }}"></script>
  <script src="{{ assetv('js/dashboard.js') }}"></script>
</body>
</html>
