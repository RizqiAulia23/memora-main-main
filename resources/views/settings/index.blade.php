<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Settings - Memorify</title>
  <meta name="description" content="Manage your account settings." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="{{ asset('css/base.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/settings.css') }}">
</head>
<body>

  <div class="dash-layout">

    @include('partials.dashboard-sidebar', ['activeSidebar' => 'settings'])

    <main class="dash-main">

      @include('partials.dashboard-topbar')

      <div class="dash-content">

        @if (session('success'))
          <div class="dash-alert dash-alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
          </div>
        @endif

        @if (session('error'))
          <div class="dash-alert dash-alert-error">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
          </div>
        @endif

        <!-- Page Header -->
        <section class="mem-head reveal">
          <div>
            <h1 class="mem-head-title">Settings</h1>
            <p class="mem-head-sub">Tune Memorify just the way you love it.</p>
          </div>
        </section>

        <div class="set-layout reveal reveal-delay-1">

          <div class="set-main">

            <!-- Preferences -->
            <section class="prof-card">
              <h2 class="prof-card-title"><i class="fas fa-palette"></i> Preferences</h2>
              <form method="POST" action="{{ route('settings.update') }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                  <label for="theme">Theme</label>
                  <div class="set-theme-options">
                    <label class="set-theme-option {{ $settings->theme === 'light' ? 'selected' : '' }}">
                      <input type="radio" name="theme" value="light" {{ $settings->theme === 'light' ? 'checked' : '' }} />
                      <i class="fas fa-sun"></i> Light
                    </label>
                    <label class="set-theme-option {{ $settings->theme === 'dark' ? 'selected' : '' }}">
                      <input type="radio" name="theme" value="dark" {{ $settings->theme === 'dark' ? 'checked' : '' }} />
                      <i class="fas fa-moon"></i> Dark
                    </label>
                  </div>
                  @error('theme')
                    <span class="form-error">{{ $message }}</span>
                  @enderror
                </div>

                <div class="form-group set-toggle-row">
                  <div>
                    <label for="notifications_enabled">Notifications</label>
                    <p class="set-hint">Reminders about anniversaries and new memories.</p>
                  </div>
                  <label class="set-switch">
                    <input type="checkbox" id="notifications_enabled" name="notifications_enabled" value="1" {{ $settings->notifications_enabled ? 'checked' : '' }} />
                    <span class="set-switch-track"></span>
                  </label>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
              </form>
            </section>

            <!-- Storage -->
            <section class="prof-card">
              <h2 class="prof-card-title"><i class="fas fa-hdd"></i> Storage</h2>
              <div class="set-storage">
                <div class="set-storage-bar"><span style="width: {{ min(100, max(5, ($storageUsed !== '0 B' ? 30 : 0))) }}%"></span></div>
                <p class="set-hint">You are currently using <strong>{{ $storageUsed }}</strong> for your photo memories.</p>
                <a href="{{ route('gallery.index') }}" class="btn btn-outline btn-sm"><i class="fas fa-images"></i> View Gallery</a>
              </div>
            </section>

          </div>

          <aside class="set-main">

            <!-- Danger Zone -->
            <section class="prof-card set-danger">
              <h2 class="prof-card-title"><i class="fas fa-exclamation-triangle"></i> Danger Zone</h2>
              <p class="set-hint">Deleting your account is permanent. All memories, photos, favorites, and love letters will be removed.</p>
              <form method="POST" action="{{ route('settings.delete-account') }}" onsubmit="return confirm('Are you absolutely sure? This will permanently delete your account and all your data.');">
                @csrf
                @method('DELETE')
                <div class="form-group">
                  <label for="delete_password">Confirm your password to continue</label>
                  <input type="password" id="delete_password" name="password" class="form-control @error('password') is-invalid @enderror" />
                  @error('password')
                    <span class="form-error">{{ $message }}</span>
                  @enderror
                </div>
                <button type="submit" class="btn btn-danger"><i class="fas fa-user-slash"></i> Delete My Account</button>
              </form>
            </section>

          </aside>
        </div>

      </div>
    </main>

  </div>

  <div class="toast-container" id="toast-container" aria-live="polite"></div>

  <script src="{{ asset('js/main.js') }}"></script>
  <script src="{{ asset('js/dashboard.js') }}"></script>
  <script src="{{ asset('js/settings.js') }}"></script>
</body>
</html>
