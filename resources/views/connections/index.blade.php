<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>Connections - Memorify</title>
  <meta name="description" content="Connect with people on Memorify." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="{{ assetv('css/base.css') }}">
  <link rel="stylesheet" href="{{ assetv('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ assetv('css/connections.css') }}">
  <link rel="stylesheet" href="{{ assetv('css/couple.css') }}">
</head>
<body>

  <div class="dash-layout">

    @include('partials.dashboard-sidebar', ['activeSidebar' => 'connections'])

    <main class="dash-main">

      @include('partials.dashboard-topbar')

      <div class="dash-content">

        @include('partials.flash-alerts')

        <!-- Page Header -->
        <section class="mem-head reveal" data-gsap-reveal>
          <div>
            <h1 class="mem-head-title"><i class="fas fa-user-friends conn-head-icon"></i> Connections</h1>
            <p class="mem-head-sub">Connect with people and keep your memories close.</p>
          </div>
        </section>

        <!-- Your Connection Code -->
        <section class="dash-section conn-section reveal" aria-label="Your connection code" data-gsap-reveal>
          <div class="dash-section-header">
            <h3><i class="fas fa-hashtag"></i> Your Connection Code</h3>
          </div>
          <div class="conn-code-box">
            <div class="conn-code-display" id="conn-code" aria-label="Your connection code">{{ $user->connection_code }}</div>
            <button type="button" class="btn btn-outline btn-sm" id="conn-copy-btn" data-copy-code="{{ $user->connection_code }}">
              <i class="fas fa-copy"></i> Copy
            </button>
            <p class="conn-hint">Share this 8-digit code with someone so they can connect with you.</p>
          </div>
        </section>

        <!-- Connect with a Friend -->
        <section class="dash-section conn-section reveal reveal-delay-1" aria-label="Connect with a friend" data-gsap-reveal>
          <div class="dash-section-header">
            <h3><i class="fas fa-user-plus"></i> Connect with a Friend</h3>
          </div>
          <form method="POST" action="{{ route('connections.store') }}" class="conn-search-form" data-connection-action role="form">
            @csrf
            <input
              type="text"
              name="connection_code"
              value="{{ old('connection_code') }}"
              placeholder="Enter Connection Code"
              inputmode="numeric"
              pattern="[0-9]{8}"
              maxlength="8"
              class="conn-search-input"
              aria-label="Enter connection code"
              autocomplete="off"
            />
            <button type="submit" class="btn btn-primary">Connect</button>
          </form>
          @error('connection_code')
            <p class="conn-error">{{ $message }}</p>
          @enderror
        </section>

        <!-- Incoming Requests -->
        <section class="dash-section conn-section reveal reveal-delay-2" aria-label="Incoming requests" data-gsap-reveal>
          <div class="dash-section-header">
            <h3><i class="fas fa-inbox"></i> Incoming Requests</h3>
          </div>
          @if ($incoming->isNotEmpty())
            @foreach ($incoming as $connection)
              @php $other = $connection->sender; @endphp
              <div class="conn-row">
                @if ($other->avatarUrl())
                  <img src="{{ $other->avatarUrl() }}" alt="{{ $other->name }}'s avatar" class="conn-avatar" />
                @else
                  <div class="conn-avatar conn-avatar-fallback">{{ strtoupper(substr($other->name, 0, 1)) }}</div>
                @endif
                <span class="conn-name">{{ $other->name }}</span>
                <span class="conn-status-text"><i class="fas fa-hourglass-half"></i> Pending</span>
                <span class="conn-row-actions">
                  <form method="POST"
                        action="{{ route('connections.accept', $connection) }}"
                        data-connection-action
                        data-connection-method="PATCH"
                        class="conn-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-primary btn-sm">
                      <i class="fas fa-check"></i> Accept
                    </button>
                  </form>
                  <form method="POST"
                        action="{{ route('connections.reject', $connection) }}"
                        data-connection-action
                        data-connection-method="PATCH"
                        class="conn-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-outline btn-sm">
                      <i class="fas fa-times"></i> Reject
                    </button>
                  </form>
                </span>
              </div>
            @endforeach
          @else
            <div class="dash-empty">
              <div class="dash-empty-icon"><i class="fas fa-inbox"></i></div>
              <p>No incoming requests right now.</p>
            </div>
          @endif
        </section>

        <!-- Outgoing Requests -->
        <section class="dash-section conn-section reveal reveal-delay-3" aria-label="Outgoing requests" data-gsap-reveal>
          <div class="dash-section-header">
            <h3><i class="fas fa-paper-plane"></i> Outgoing Requests</h3>
          </div>
          @if ($outgoing->isNotEmpty())
            @foreach ($outgoing as $connection)
              @php $other = $connection->receiver; @endphp
              <div class="conn-row">
                @if ($other->avatarUrl())
                  <img src="{{ $other->avatarUrl() }}" alt="{{ $other->name }}'s avatar" class="conn-avatar" />
                @else
                  <div class="conn-avatar conn-avatar-fallback">{{ strtoupper(substr($other->name, 0, 1)) }}</div>
                @endif
                <span class="conn-name">{{ $other->name }}</span>
                <span class="conn-status-text"><i class="fas fa-hourglass-half"></i> Pending</span>
                <span class="conn-row-actions">
                  <form method="POST"
                        action="{{ route('connections.destroy', $connection) }}"
                        data-connection-action
                        data-connection-method="DELETE">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline btn-sm">
                      <i class="fas fa-ban"></i> Cancel
                    </button>
                  </form>
                </span>
              </div>
            @endforeach
          @else
            <div class="dash-empty">
              <div class="dash-empty-icon"><i class="fas fa-paper-plane"></i></div>
              <p>No outgoing requests right now.</p>
            </div>
          @endif
        </section>

        <!-- My Connections -->
        <section class="dash-section conn-section reveal reveal-delay-4" aria-label="My connections" data-gsap-reveal>
          <div class="dash-section-header">
            <h3><i class="fas fa-user-check"></i> My Connections</h3>
          </div>
          @if ($connected->isNotEmpty())
            @foreach ($connected as $connection)
              @php
                $other = $connection->sender_id === auth()->id() ? $connection->receiver : $connection->sender;
              @endphp
              <div class="conn-row conn-pair">
                <div class="conn-pair-main">
                  <div class="conn-pair-user">
                    @if ($user->avatarUrl())
                      <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}'s avatar" class="conn-avatar" />
                    @else
                      <div class="conn-avatar conn-avatar-fallback">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                    @endif
                    <span class="conn-name conn-pair-name">{{ $user->name }}</span>
                  </div>

                  <div class="connection-heart" aria-hidden="true">
                    <i class="fas fa-heart"></i>
                    <span class="conn-status-text conn-status-ok"><i class="fas fa-link"></i> Connected</span>
                  </div>

                  <div class="conn-pair-user">
                    @if ($other->avatarUrl())
                      <img src="{{ $other->avatarUrl() }}" alt="{{ $other->name }}'s avatar" class="conn-avatar" />
                    @else
                      <div class="conn-avatar conn-avatar-fallback">{{ strtoupper(substr($other->name, 0, 1)) }}</div>
                    @endif
                    <span class="conn-name conn-pair-name">{{ $other->name }}</span>
                  </div>
                </div>

                <div class="conn-pair-tools">
                  <a href="{{ route('calendar.index') }}" class="conn-tool-btn">
                    <i class="fas fa-calendar-alt"></i> Calendar
                  </a>
                  <a href="{{ route('important-dates.index') }}" class="conn-tool-btn">
                    <i class="fas fa-calendar-heart"></i> Important Dates
                  </a>
                  <a href="{{ route('playlists.index') }}" class="conn-tool-btn">
                    <i class="fas fa-headphones"></i> Playlist
                  </a>
                  <a href="{{ route('bucket-list.index') }}" class="conn-tool-btn">
                    <i class="fas fa-list-check"></i> Bucket List
                  </a>
                  <a href="{{ route('couple-timeline.index') }}" class="conn-tool-btn">
                    <i class="fas fa-heart-circle-plus"></i> Couple Timeline
                  </a>
                  <a href="{{ route('letters.create', ['receiver_id' => $other->id]) }}" class="conn-tool-btn">
                    <i class="fas fa-feather-alt"></i> Love Letter
                  </a>
                  <a href="{{ route('shared-memories.index', ['partner' => $other->id]) }}" class="conn-tool-btn">
                    <i class="fas fa-share-nodes"></i> Shared Memories
                  </a>
                  <form method="POST"
                        action="{{ route('connections.destroy', $connection) }}"
                        data-connection-action
                        data-connection-method="DELETE"
                        data-connection-confirm="Disconnect from {{ $other->name }}?">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="conn-tool-btn">
                      <i class="fas fa-unlink"></i> Disconnect
                    </button>
                  </form>
                </div>
              </div>
            @endforeach
          @else
            <div class="dash-empty">
              <div class="dash-empty-icon"><i class="fas fa-user-friends"></i></div>
              <p>You are not connected with anyone yet. Share your 8-digit connection code to get started.</p>
            </div>
          @endif
        </section>

      </div>
    </main>

  </div>

  <div class="toast-container" id="toast-container" aria-live="polite"></div>

  @vite('resources/js/memorify-animations.js')
  @vite('resources/js/connections-animations.js')
  <script src="{{ assetv('js/main.js') }}"></script>
  <script src="{{ assetv('js/dashboard.js') }}"></script>
  <script src="{{ assetv('js/connections.js') }}"></script>
</body>
</html>
