<header class="dash-topbar">
  <button class="dash-mobile-menu-btn" id="dash-mobile-menu-btn" aria-label="Toggle sidebar">
    <i class="fas fa-bars"></i>
  </button>

  <form class="dash-search" action="{{ route('memories.index') }}" method="GET" role="search">
    <i class="fas fa-search"></i>
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search memories, letters, moments..." aria-label="Search" />
  </form>

  <div class="dash-topbar-actions">
    <button class="dash-topbar-btn" aria-label="Notifications">
      <i class="fas fa-bell"></i>
      <span class="dot"></span>
    </button>
    <button class="dash-topbar-btn" aria-label="Messages">
      <i class="fas fa-comment-dots"></i>
    </button>
    <img src="https://i.pravatar.cc/80?img=32" alt="User avatar" class="dash-topbar-avatar" />
  </div>
</header>
