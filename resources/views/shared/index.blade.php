<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Shared Memories - Memorify</title>
  <meta name="description" content="Memories shared with the people you love." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="{{ assetv('css/base.css') }}">
  <link rel="stylesheet" href="{{ assetv('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ assetv('css/memories.css') }}">
  <link rel="stylesheet" href="{{ assetv('css/shared.css') }}">
</head>
<body>

  <div class="dash-layout">

    @include('partials.dashboard-sidebar', ['activeSidebar' => 'shared-memories'])

    <main class="dash-main">

      @include('partials.dashboard-topbar')

      <div class="dash-content">

        @include('partials.flash-alerts')

        <!-- Page Header -->
        <section class="mem-head reveal" data-gsap-reveal>
          <div>
            <h1 class="mem-head-title"><i class="fas fa-share-nodes shm-head-icon"></i> Shared Memories</h1>
            <p class="mem-head-sub">Memories you keep together with the people you love.</p>
          </div>
          <a href="{{ route('memories.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Memory
          </a>
        </section>

        <!-- Partner Filter -->
        @if ($partners->isNotEmpty())
          <form class="shm-filter reveal reveal-delay-1" action="{{ route('shared-memories.index') }}" method="GET" data-gsap-reveal>
            <label for="partner" class="shm-filter-label">Partner</label>
            <select id="partner" name="partner" class="mem-filter-select" onchange="this.form.submit()">
              <option value="">All Partners</option>
              @foreach ($partners as $partnerOption)
                <option value="{{ $partnerOption->id }}" {{ $partner?->id === $partnerOption->id ? 'selected' : '' }}>
                  {{ $partnerOption->name }}
                </option>
              @endforeach
            </select>
            @if ($partner)
              <a href="{{ route('shared-memories.index') }}" class="mem-filter-clear"><i class="fas fa-times"></i> Clear</a>
            @endif
          </form>
        @endif

        <!-- Content -->
        <section class="reveal reveal-delay-2" aria-label="Shared memories list" data-gsap-reveal>
          @if ($sharedWithMe->isEmpty() && $sharedByMe->isEmpty())
            <div class="dash-section mem-empty">
              <div class="dash-empty">
                <div class="dash-empty-icon"><i class="fas fa-share-nodes"></i></div>
                <p>No shared memories yet. Create a memory and share it with someone special.</p>
                <div class="dash-empty-actions">
                  <a href="{{ route('memories.create') }}" class="btn btn-primary btn-sm">Create Memory</a>
                  <a href="{{ route('connections.index') }}" class="btn btn-outline btn-sm">Connect with Someone</a>
                </div>
              </div>
            </div>
          @else
            @if ($sharedWithMe->isNotEmpty())
              <div class="shm-section">
                <h2 class="shm-section-title"><i class="fas fa-inbox"></i> Shared With Me</h2>
                <div class="dash-memories-grid">
                  @foreach ($sharedWithMe as $shared)
                    @php $memory = $shared->memory; @endphp
                    <article class="dash-memory-card shm-card">
                      <a href="{{ route('memories.show', $memory) }}" class="shm-card-link">
                        <div class="dash-memory-img">
                          <img src="{{ $memory->imageUrl() }}" alt="{{ $memory->title }}" loading="lazy" />
                        </div>
                        <div class="dash-memory-info">
                          <div class="dash-memory-title">{{ $memory->title }}</div>
                          <div class="dash-memory-meta">
                            <span><i class="fas fa-calendar"></i> {{ $memory->memory_date->format('M j, Y') }}</span>
                          </div>
                          <div class="shm-chips">
                            <span class="shm-chip"><i class="fas fa-user"></i> {{ $memory->user->name }}</span>
                            @foreach ($memory->sharedWith as $with)
                              <span class="shm-chip shm-chip-heart"><i class="fas fa-heart"></i> {{ $with->partner->name }}</span>
                            @endforeach
                          </div>
                        </div>
                      </a>
                    </article>
                  @endforeach
                </div>
              </div>
            @endif

            @if ($sharedByMe->isNotEmpty())
              <div class="shm-section">
                <h2 class="shm-section-title"><i class="fas fa-paper-plane"></i> Shared By Me</h2>
                <div class="dash-memories-grid">
                  @foreach ($sharedByMe as $shared)
                    @php $memory = $shared->memory; @endphp
                    <article class="dash-memory-card shm-card">
                      <a href="{{ route('memories.show', $memory) }}" class="shm-card-link">
                        <div class="dash-memory-img">
                          <img src="{{ $memory->imageUrl() }}" alt="{{ $memory->title }}" loading="lazy" />
                        </div>
                        <div class="dash-memory-info">
                          <div class="dash-memory-title">{{ $memory->title }}</div>
                          <div class="dash-memory-meta">
                            <span><i class="fas fa-calendar"></i> {{ $memory->memory_date->format('M j, Y') }}</span>
                          </div>
                          <div class="shm-chips">
                            <span class="shm-chip shm-chip-heart"><i class="fas fa-heart"></i> {{ $shared->partner->name }}</span>
                          </div>
                        </div>
                      </a>
                      <div class="shm-card-actions">
                        <a href="{{ route('memories.show', $memory) }}" class="btn btn-outline btn-sm"><i class="fas fa-book-open"></i> View</a>
                        <form method="POST" action="{{ route('shared-memories.destroy', $shared) }}" onsubmit="return confirm('Unshare this memory with {{ $shared->partner->name }}? The memory itself will not be deleted.');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-outline btn-sm shm-unshare-btn">
                            <i class="fas fa-unlink"></i> Unshare
                          </button>
                        </form>
                      </div>
                    </article>
                  @endforeach
                </div>
              </div>
            @endif
          @endif
        </section>

      </div>
    </main>

  </div>

  <div class="toast-container" id="toast-container" aria-live="polite"></div>

  @vite('resources/js/memorify-animations.js')
  @vite('resources/js/shared-animations.js')
  <script src="{{ assetv('js/main.js') }}"></script>
  <script src="{{ assetv('js/dashboard.js') }}"></script>
</body>
</html>
