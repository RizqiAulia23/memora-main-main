<header class="dash-topbar">
  <button class="dash-mobile-menu-btn" id="dash-mobile-menu-btn" aria-label="Toggle sidebar" aria-controls="dash-sidebar" aria-expanded="false">
    <i class="fas fa-bars"></i>
  </button>

  <form class="dash-search" action="{{ route('search.index') }}" method="GET" role="search" data-global-search>
    <i class="fas fa-search"></i>
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search memories, letters, photos..." aria-label="Search" data-global-search-input autocomplete="off" role="combobox" aria-expanded="false" aria-controls="global-search-results" aria-autocomplete="list" />
    <div class="search-suggest" data-global-search-results id="global-search-results" hidden></div>
  </form>

  <div class="dash-topbar-actions">
    <details class="notif-bell">
      <summary class="dash-topbar-btn notif-bell-btn" aria-label="Notifications">
        <i class="fas fa-bell"></i>
        @if ($unreadNotificationsCount > 0)
          <span class="notif-badge">{{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}</span>
        @endif
      </summary>
      <div class="notif-panel">
        <div class="notif-panel-head">
          <span>Notifications</span>
          @if ($unreadNotificationsCount > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
              @csrf
              <button type="submit" class="notif-read-all">Mark all as read</button>
            </form>
          @endif
        </div>
        <div class="notif-panel-list">
          @forelse ($recentNotifications as $notification)
            <form method="POST" action="{{ route('notifications.read', $notification) }}" class="notif-item {{ $notification->read_at ? '' : 'unread' }}">
              @csrf
              <button type="submit" class="notif-item-btn">
                <span class="notif-item-icon"><i class="fas {{ notification_icon($notification->type) }}"></i></span>
                <span class="notif-item-body">
                  <span class="notif-item-title">{{ $notification->data['title'] ?? 'Notification' }}</span>
                  <span class="notif-item-msg">{{ $notification->data['message'] ?? '' }}</span>
                  <span class="notif-item-time">{{ $notification->created_at->diffForHumans() }}</span>
                </span>
              </button>
            </form>
          @empty
            <div class="notif-empty">
              <i class="fas fa-bell-slash"></i>
              <p>No notifications yet.</p>
            </div>
          @endforelse
        </div>
        <a href="{{ route('notifications.index') }}" class="notif-panel-foot">View all notifications</a>
      </div>
    </details>
    <a href="{{ route('memories.create') }}" class="dash-topbar-btn" aria-label="Add memory">
      <i class="fas fa-plus"></i>
    </a>
    @if (auth()->user()->avatarUrl())
      <a href="{{ route('profile.show') }}" aria-label="View profile">
        <img src="{{ auth()->user()->avatarUrl() }}" alt="User avatar" class="dash-topbar-avatar" />
      </a>
    @else
      <a href="{{ route('profile.show') }}" class="dash-topbar-avatar dash-topbar-avatar-fallback" aria-label="View profile">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</a>
    @endif
  </div>
</header>