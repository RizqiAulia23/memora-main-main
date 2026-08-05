@php
    $active = $activeSidebar ?? 'dashboard';
    $memoryCount = auth()->user()->memories()->count();
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
    <a href="{{ route('memories.index') }}" class="dash-sidebar-link {{ $active === 'memories' ? 'active' : '' }}">
      <i class="fas fa-images"></i> Memories
      @if ($memoryCount > 0)
        <span class="badge">{{ $memoryCount }}</span>
      @endif
    </a>
    <a href="#" class="dash-sidebar-link">
      <i class="fas fa-stream"></i> Timeline
    </a>
    <a href="#" class="dash-sidebar-link">
      <i class="fas fa-heart"></i> Favorites
    </a>
    <a href="#" class="dash-sidebar-link">
      <i class="fas fa-envelope-open-text"></i> Love Letters
    </a>
    <a href="#" class="dash-sidebar-link">
      <i class="fas fa-box-open"></i> Photobox
    </a>

    <div class="dash-sidebar-section">Tools</div>
    <a href="#" class="dash-sidebar-link">
      <i class="fas fa-calendar-alt"></i> Calendar
    </a>

    <div class="dash-sidebar-section">Account</div>
    <a href="#" class="dash-sidebar-link">
      <i class="fas fa-user-circle"></i> Profile
    </a>
    <a href="#" class="dash-sidebar-link">
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
      <img src="https://i.pravatar.cc/80?img=32" alt="User avatar" />
      <div class="dash-sidebar-user-info">
        <div class="dash-sidebar-user-name">{{ auth()->user()->name }}</div>
        <div class="dash-sidebar-user-email">{{ auth()->user()->email }}</div>
      </div>
    </div>
  </div>
</aside>

<div class="dash-sidebar-overlay" id="dash-sidebar-overlay"></div>
