<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Memories - Memorify</title>
  <meta name="description" content="Browse and manage your memories." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="{{ asset('css/base.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/memories.css') }}">
</head>
<body>

  <div class="dash-layout">

    @include('partials.dashboard-sidebar', ['activeSidebar' => 'memories'])

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
            <h1 class="mem-head-title">My Memories</h1>
            <p class="mem-head-sub">{{ $memories->total() }} {{ Str::plural('memory', $memories->total()) }} preserved so far.</p>
          </div>
          <a href="{{ route('memories.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Memory
          </a>
        </section>

        <!-- Search & Filter -->
        <section class="mem-toolbar reveal reveal-delay-1">
          <form class="mem-search" action="{{ route('memories.index') }}" method="GET" role="search">
            <i class="fas fa-search"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title or description..." aria-label="Search memories" />
          </form>
          <form class="mem-filter" action="{{ route('memories.index') }}" method="GET">
            <input type="hidden" name="search" value="{{ request('search') }}" />
            <label for="sort" class="mem-filter-label">Sort by</label>
            <select id="sort" name="sort" class="mem-filter-select" onchange="this.form.submit()">
              <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest</option>
              <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest</option>
              <option value="memory_date" {{ request('sort') === 'memory_date' ? 'selected' : '' }}>Memory Date</option>
            </select>
            @if (request('search') || request('sort'))
              <a href="{{ route('memories.index') }}" class="mem-filter-clear"><i class="fas fa-times"></i> Clear</a>
            @endif
          </form>
        </section>

        <!-- Memories Grid -->
        <section class="reveal reveal-delay-2" aria-label="Memories list">
          @if ($memories->isNotEmpty())
            <div class="dash-memories-grid">
              @foreach ($memories as $memory)
                <article class="dash-memory-card" onclick="window.location='{{ route('memories.show', $memory) }}'">
                  <div class="dash-memory-img">
                    <img src="{{ $memory->image ? asset('storage/' . $memory->image) : asset('img/memory-placeholder.svg') }}" alt="{{ $memory->title }}" loading="lazy" />
                    <span class="mem-card-actions">
                      <a href="{{ route('memories.edit', $memory) }}" class="mem-card-btn" aria-label="Edit memory" onclick="event.stopPropagation()"><i class="fas fa-pen"></i></a>
                      <form method="POST" action="{{ route('memories.destroy', $memory) }}" class="mem-card-form" onsubmit="return confirm('Delete this memory?');" onclick="event.stopPropagation()">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="mem-card-btn mem-card-btn-danger" aria-label="Delete memory"><i class="fas fa-trash"></i></button>
                      </form>
                    </span>
                  </div>
                  <div class="dash-memory-info">
                    <div class="dash-memory-title">{{ $memory->title }}</div>
                    <div class="dash-memory-meta">
                      <span><i class="fas fa-calendar"></i> {{ $memory->memory_date->format('M j, Y') }}</span>
                    </div>
                  </div>
                </article>
              @endforeach
            </div>
          @else
            <div class="dash-section mem-empty">
              <div class="dash-empty">
                <div class="dash-empty-icon"><i class="fas fa-images"></i></div>
                <p>{{ request('search') ? 'No memories found for "' . e(request('search')) . '".' : 'No memories yet. Start preserving your beautiful moments today.' }}</p>
                <a href="{{ route('memories.create') }}" class="btn btn-primary btn-sm">Add Your First Memory</a>
              </div>
            </div>
          @endif

          <!-- Pagination -->
          @if ($memories->hasPages())
            <div class="mem-pagination-wrap">
              {{ $memories->links('vendor.pagination.memorify') }}
            </div>
          @endif
        </section>

      </div>
    </main>

  </div>

  <script src="{{ asset('js/main.js') }}"></script>
  <script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>
