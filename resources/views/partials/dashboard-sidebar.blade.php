@php
    $active = $activeSidebar ?? 'dashboard';
    $memoryCount = auth()->user()->memories()->count();
    $avatarUrl = auth()->user()->avatarUrl();
@endphp
<aside class="dash-sidebar" id="dash-sidebar" aria-label="Sidebar navigation">
  <div class="dash-sidebar-logo">
    <div class="logo-icon"><i class="fas fa-heart"></i></div>
    <span class="brand">Memorify</span>
  </div>

  <nav class="dash-sidebar-nav">
    <div class="dash-sidebar-section">Main</div>
    <a href="{{ route('dashboard') }}" class="dash-sidebar-link {{ $active === 'dashboard' ? 'active' : '' }}">
      <i class="fas fa-th-large"></i> Dashboard
    </a>
    <a href="{{ route('memories.index') }}" class="dash-sidebar-link {{ in_array($active, ['memories', 'memories.show', 'memories.edit']) ? 'active' : '' }}">
      <i class="fas fa-images"></i> Memories
      @if ($memoryCount > 0)
        <span class="badge">{{ $memoryCount }}</span>
      @endif
    </a>
    <a href="{{ route('timeline.index') }}" class="dash-sidebar-link {{ $active === 'timeline' ? 'active' : '' }}">
      <i class="fas fa-stream"></i> Timeline
    </a>
    <a href="{{ route('favorites.index') }}" class="dash-sidebar-link {{ $active === 'favorites' ? 'active' : '' }}">
      <i class="fas fa-heart"></i> Favorites
    </a>
    <a href="{{ route('letters.index') }}" class="dash-sidebar-link {{ in_array($active, ['letters', 'letters.show', 'letters.create', 'letters.edit']) ? 'active' : '' }}">
      <i class="fas fa-envelope-open-text"></i> Love Letters
    </a>
    <a href="{{ route('gallery.index') }}" class="dash-sidebar-link {{ $active === 'gallery' ? 'active' : '' }}">
      <i class="fas fa-camera-retro"></i> Gallery
    </a>

    <div class="dash-sidebar-section">Tools</div>
    <a href="{{ route('calendar.index') }}" class="dash-sidebar-link {{ $active === 'calendar' ? 'active' : '' }}">
      <i class="fas fa-calendar-alt"></i> Calendar
    </a>

    <div class="dash-sidebar-section">Account</div>
    <a href="{{ route('profile.show') }}" class="dash-sidebar-link {{ $active === 'profile' ? 'active' : '' }}">
      <i class="fas fa-user-circle"></i> Profile
    </a>
    <a href="{{ route('settings.index') }}" class="dash-sidebar-link {{ $active === 'settings' ? 'active' : '' }}">
      <i class="fas fa-cog"></i> Settings
    </a>
  </nav>

  <div class="dash-sidebar-footer">
    <form method="POST" action="{{ route('logout') }}" class="dash-sidebar-logout">
      @csrf
      <button type="submit" class="dash-sidebar-link dash-sidebar-link-btn">
        <i class="fas fa-sign-out-alt"></i> Logout
      </button>
    </form>
    <div class="dash-sidebar-user">
      @if ($avatarUrl)
        <img src="{{ $avatarUrl }}" alt="User avatar" />
      @else
        <div class="dash-sidebar-avatar-fallback">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
      @endif
      <div class="dash-sidebar-user-info">
        <div class="dash-sidebar-user-name">{{ auth()->user()->name }}</div>
        <div class="dash-sidebar-user-email">{{ auth()->user()->email }}</div>
      </div>
    </div>
  </div>
</aside>

<div class="dash-sidebar-overlay" id="dash-sidebar-overlay"></div>
