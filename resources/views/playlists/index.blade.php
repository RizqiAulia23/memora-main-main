<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Shared Playlist - Memorify</title>
  <meta name="description" content="Songs that sound like us." />
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

    @include('partials.dashboard-sidebar', ['activeSidebar' => 'playlists'])

    <main class="dash-main">

      @include('partials.dashboard-topbar')

      <div class="dash-content">

        @include('partials.flash-alerts')

        <!-- Page Header -->
        <section class="mem-head reveal" data-gsap-reveal>
          <div>
            <h1 class="mem-head-title"><i class="fas fa-headphones pl-head-icon"></i> Shared Playlist</h1>
            <p class="mem-head-sub">Songs that sound like us.</p>
          </div>
        </section>

        @if ($partners->isEmpty())
          <section class="reveal reveal-delay-1" aria-label="Create a playlist" data-gsap-reveal>
            <div class="dash-empty">
              <div class="dash-empty-icon"><i class="fas fa-headphones"></i></div>
              <p>Connect with your partner first, then build a playlist together.</p>
            </div>
          </section>
        @else
          <!-- Create Playlist -->
          <section class="reveal reveal-delay-1" aria-label="Create a playlist" data-gsap-reveal>
            <div class="pl-form-wrap">
              <h3 class="cpl-feed-title"><i class="fas fa-music"></i> Start a Playlist</h3>
              <form method="POST" action="{{ route('playlists.store') }}" data-submit-feedback>
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
                    <label for="name">Playlist name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" maxlength="80" placeholder="Our Song List" required />
                    @error('name')
                      <span class="form-error">{{ $message }}</span>
                    @enderror
                  </div>
                </div>
                <div class="shm-form-actions">
                  <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Create Playlist</button>
                </div>
              </form>
            </div>
          </section>
        @endif

        <!-- Playlists -->
        <section class="reveal reveal-delay-2" aria-label="Your shared playlists" data-gsap-reveal>
          <div class="cpl-feed-section">
            <div class="dash-section-header">
              <h3><i class="fas fa-compact-disc"></i> Your Playlists</h3>
            </div>

            @if ($playlists->isEmpty())
              <div class="dash-empty">
                <div class="dash-empty-icon"><i class="fas fa-music"></i></div>
                <p>No playlists yet. Create one and add the songs that mean something to you both.</p>
              </div>
            @else
              <div class="pl-grid">
                @foreach ($playlists as $playlist)
                  <article class="pl-card">
                    <div class="pl-card-icon"><i class="fas fa-heart"></i></div>
                    <div class="pl-card-name">{{ $playlist->name }}</div>
                    <div class="pl-card-meta">
                      @if ($playlist->user_id === auth()->id())
                        With {{ $playlist->partner->name }}
                      @else
                        Started by {{ $playlist->user->name }}
                      @endif
                      &middot; {{ $playlist->tracks->count() }} track{{ $playlist->tracks->count() === 1 ? '' : 's' }}
                    </div>

                    @if ($playlist->tracks->isNotEmpty())
                      <div class="pl-track-list">
                        @foreach ($playlist->tracks as $track)
                          <div class="pl-track">
                            <div class="pl-track-thumb">
                              @if ($track->thumbnail)
                                <img src="{{ $track->thumbnail }}" alt="" loading="lazy" />
                              @else
                                <i class="fas fa-music"></i>
                              @endif
                            </div>
                            <div class="pl-track-body">
                              <div class="pl-track-title">
                                <a href="{{ $track->url }}" target="_blank" rel="noopener noreferrer">{{ $track->title }}</a>
                              </div>
                              <div class="pl-track-artist">{{ $track->artist }}</div>
                              <div class="pl-track-added">
                                <i class="fas fa-user-plus"></i> {{ $track->added_by === auth()->id() ? 'You' : $track->adder->name }}
                              </div>
                            </div>
                            @if ($track->added_by === auth()->id() || $playlist->user_id === auth()->id())
                              <div class="pl-track-actions">
                                <form method="POST" action="{{ route('playlists.tracks.destroy', [$playlist, $track]) }}" onsubmit="return confirm('Remove this track?');">
                                  @csrf
                                  @method('DELETE')
                                  <button type="submit" class="pl-icon-btn pl-icon-btn-danger" title="Remove track" aria-label="Remove {{ $track->title }}"><i class="fas fa-trash"></i></button>
                                </form>
                              </div>
                            @endif
                          </div>
                        @endforeach
                      </div>
                    @endif

                    <!-- Add track -->
                    <form method="POST" action="{{ route('playlists.tracks.store', $playlist) }}" class="pl-add-form">
                      @csrf
                      <input type="text" name="title" placeholder="Song title" class="form-control" maxlength="120" required />
                      <input type="text" name="artist" placeholder="Artist" class="form-control" maxlength="120" required />
                      <input type="url" name="url" placeholder="https://..." class="form-control full" maxlength="500" required />
                      <input type="url" name="thumbnail" placeholder="Cover image URL (optional)" class="form-control full" maxlength="500" />
                      <div class="shm-form-actions full">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Track</button>
                      </div>
                    </form>

                    @if ($playlist->user_id === auth()->id())
                      <div class="pl-card-actions">
                        <a href="{{ route('playlists.edit', $playlist) }}" class="pl-icon-btn" title="Rename"><i class="fas fa-pen"></i></a>
                        <form method="POST" action="{{ route('playlists.destroy', $playlist) }}" onsubmit="return confirm('Delete this playlist?');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="pl-icon-btn pl-icon-btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                        </form>
                      </div>
                    @endif
                  </article>
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
  @vite('resources/js/playlists-animations.js')
  <script src="{{ assetv('js/main.js') }}"></script>
  <script src="{{ assetv('js/dashboard.js') }}"></script>
</body>
</html>
