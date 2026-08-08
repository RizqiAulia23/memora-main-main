<header class="dash-topbar">
  <button class="dash-mobile-menu-btn" id="dash-mobile-menu-btn" aria-label="Toggle sidebar">
    <i class="fas fa-bars"></i>
  </button>

  <form class="dash-search" action="{{ route('search.index') }}" method="GET" role="search" data-global-search>
    <i class="fas fa-search"></i>
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search memories, letters, photos..." aria-label="Search" data-global-search-input autocomplete="off" />
    <div class="search-suggest" data-global-search-results hidden></div>
  </form>

  <div class="dash-topbar-actions">
    <a href="{{ route('settings.index') }}" class="dash-topbar-btn" aria-label="Notifications">
      <i class="fas fa-bell"></i>
      <span class="dot"></span>
    </a>
    <a href="{{ route('memories.create') }}" class="dash-topbar-btn" aria-label="Add memory">
      <i class="fas fa-plus"></i>
    </a>
    @if (auth()->user()->avatarUrl())
      <img src="{{ auth()->user()->avatarUrl() }}" alt="User avatar" class="dash-topbar-avatar" />
    @else
      <div class="dash-topbar-avatar dash-topbar-avatar-fallback">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
    @endif
  </div>
</header>
