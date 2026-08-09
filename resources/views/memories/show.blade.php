<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $memory->title }} - Memorify</title>
  <meta name="description" content="{{ $memory->description }}" />
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

        <!-- Memory Detail -->
        <section class="mem-detail reveal">
          <div class="mem-detail-media">
            <img src="{{ $memory->imageUrl() }}" alt="{{ $memory->title }}" />
          </div>

          <div class="mem-detail-body">
            <div class="mem-detail-header">
              <div>
                <span class="mem-detail-date"><i class="fas fa-calendar"></i> {{ $memory->memory_date->format('F j, Y') }}</span>
                <h1 class="mem-detail-title">{{ $memory->title }}</h1>
              </div>
              <div class="mem-detail-actions">
                <a href="{{ route('memories.edit', $memory) }}" class="btn btn-outline btn-sm">
                  <i class="fas fa-pen"></i> Edit
                </a>
                <form method="POST" action="{{ route('memories.destroy', $memory) }}" onsubmit="return confirm('Delete this memory?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm mem-btn-danger">
                    <i class="fas fa-trash"></i> Delete
                  </button>
                </form>
              </div>
            </div>

            <div class="mem-detail-description">
              <p>{!! nl2br(e($memory->description)) !!}</p>
            </div>

            <div class="mem-detail-meta">
              <span><i class="fas fa-user"></i> Added by {{ $memory->user->name }}</span>
              <span><i class="far fa-clock"></i> {{ $memory->created_at->diffForHumans() }}</span>
            </div>

            <div class="mem-detail-footer">
              <a href="{{ route('memories.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Memories
              </a>
            </div>
          </div>
        </section>

      </div>
    </main>

  </div>

  <script src="{{ asset('js/main.js') }}"></script>
  <script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>
