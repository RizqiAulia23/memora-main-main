<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Notifications - Memorify</title>
  <meta name="description" content="Your notifications." />
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

    @include('partials.dashboard-sidebar', ['activeSidebar' => 'notifications'])

    <main class="dash-main">

      @include('partials.dashboard-topbar')

      <div class="dash-content">

        @include('partials.flash-alerts')

        <!-- Page Header -->
        <section class="mem-head reveal" data-gsap-reveal>
          <div>
            <h1 class="mem-head-title"><i class="fas fa-bell notif-head-icon"></i> Notifications</h1>
            <p class="mem-head-sub">Everything that needs your attention, in one place.</p>
          </div>
          @if ($notifications->isNotEmpty())
            <form method="POST" action="{{ route('notifications.read-all') }}">
              @csrf
              <button type="submit" class="btn btn-outline"><i class="fas fa-check-double"></i> Mark All as Read</button>
            </form>
          @endif
        </section>

        <!-- List -->
        <section class="reveal reveal-delay-1" aria-label="Notifications list" data-gsap-reveal>
          @if ($notifications->isNotEmpty())
            <div class="notif-page-list">
              @foreach ($notifications as $notification)
                <form method="POST" action="{{ route('notifications.read', $notification) }}" class="notif-page-item {{ $notification->read_at ? '' : 'unread' }}">
                  @csrf
                  <button type="submit" class="notif-page-btn">
                    <span class="notif-page-icon"><i class="fas {{ notification_icon($notification->type) }}"></i></span>
                    <span class="notif-page-body">
                      <span class="notif-page-title">{{ $notification->data['title'] ?? 'Notification' }}</span>
                      <span class="notif-page-msg">{{ $notification->data['message'] ?? '' }}</span>
                      <span class="notif-page-time">{{ $notification->created_at->diffForHumans() }}</span>
                    </span>
                    @if (! $notification->read_at)
                      <span class="notif-page-dot" aria-label="Unread"></span>
                    @endif
                  </button>
                </form>
              @endforeach
            </div>

            <div class="mem-pagination-wrap">
              {{ $notifications->links('vendor.pagination.memorify') }}
            </div>
          @else
            <div class="dash-section mem-empty">
              <div class="dash-empty">
                <div class="dash-empty-icon"><i class="fas fa-bell-slash"></i></div>
                <p>No notifications yet. Moments with your partner will show up here.</p>
              </div>
            </div>
          @endif
        </section>

      </div>
    </main>

  </div>

  <div class="toast-container" id="toast-container" aria-live="polite"></div>

  @vite('resources/js/memorify-animations.js')
  @vite('resources/js/notifications-animations.js')
  <script src="{{ assetv('js/main.js') }}"></script>
  <script src="{{ assetv('js/dashboard.js') }}"></script>
</body>
</html>
