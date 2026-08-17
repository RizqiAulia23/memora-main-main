<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Share Memory - Memorify</title>
  <meta name="description" content="Share a memory with someone you love." />
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
            <h1 class="mem-head-title">Share Memory</h1>
            <p class="mem-head-sub">Share a beautiful moment with someone special.</p>
          </div>
          <a href="{{ route('memories.show', $memory) }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Memory</a>
        </section>

        <section class="shm-form-wrap reveal reveal-delay-1" data-gsap-reveal>
          <form method="POST" action="{{ route('shared-memories.store', $memory) }}" class="shm-form" data-submit-feedback>
            @csrf

            <!-- Memory Preview -->
            <div class="shm-preview">
              <div class="shm-preview-img">
                <img src="{{ $memory->imageUrl() }}" alt="{{ $memory->title }}" />
              </div>
              <div class="shm-preview-info">
                <div class="shm-preview-title">{{ $memory->title }}</div>
                <div class="shm-preview-meta">
                  <span><i class="fas fa-calendar"></i> {{ $memory->memory_date->format('F j, Y') }}</span>
                </div>
                <div class="shm-preview-note">
                  <i class="fas fa-info-circle"></i> The original memory stays yours — sharing only opens it for your partner.
                </div>
              </div>
            </div>

            @if ($partners->isNotEmpty())
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

              <div class="shm-form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-heart"></i> Share Memory</button>
                <a href="{{ route('memories.show', $memory) }}" class="btn btn-outline">Cancel</a>
              </div>
            @else
              <div class="shm-no-partners">
                <div class="dash-empty-icon"><i class="fas fa-user-friends"></i></div>
                <p>No one to share with right now — this memory is already shared with all of your connected partners.</p>
                <div class="dash-empty-actions">
                  <a href="{{ route('connections.index') }}" class="btn btn-outline btn-sm">Go to Connections</a>
                  <a href="{{ route('memories.show', $memory) }}" class="btn btn-primary btn-sm">Back to Memory</a>
                </div>
              </div>
            @endif
          </form>
        </section>

      </div>
    </main>

  </div>

  <div class="toast-container" id="toast-container" aria-live="polite"></div>

  @vite('resources/js/memorify-animations.js')
  @vite('resources/js/shared-form-animations.js')
  <script src="{{ assetv('js/main.js') }}"></script>
  <script src="{{ assetv('js/dashboard.js') }}"></script>
</body>
</html>
