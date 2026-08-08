<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Profile - Memorify</title>
  <meta name="description" content="Your profile." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="{{ asset('css/base.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
</head>
<body>

  <div class="dash-layout">

    @include('partials.dashboard-sidebar', ['activeSidebar' => 'profile'])

    <main class="dash-main">

      @include('partials.dashboard-topbar')

      <div class="dash-content">

        @if (session('success'))
          <div class="dash-alert dash-alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
          </div>
        @endif

        <!-- Page Header -->
        <section class="mem-head reveal">
          <div>
            <h1 class="mem-head-title">Your Profile</h1>
            <p class="mem-head-sub">Tell your story and keep it up to date.</p>
          </div>
        </section>

        <div class="prof-layout reveal reveal-delay-1">

          <!-- Avatar Card -->
          <aside class="prof-card prof-avatar-card">
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" data-avatar-form>
              @csrf
              @method('PUT')
              <div class="prof-avatar">
                @if ($user->avatarUrl())
                  <img src="{{ $user->avatarUrl() }}" alt="Your avatar" data-avatar-preview />
                @else
                  <div class="prof-avatar-fallback" data-avatar-preview>{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                @endif
                <label class="prof-avatar-edit" for="avatar" title="Change avatar">
                  <i class="fas fa-camera"></i>
                  <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp" hidden data-avatar-input />
                </label>
              </div>
              <p class="prof-avatar-name">{{ $user->name }}</p>
              <p class="prof-avatar-email">{{ $user->email }}</p>
              @error('avatar')
                <span class="form-error">{{ $message }}</span>
              @enderror
              <button type="submit" class="btn btn-primary btn-sm" data-avatar-save hidden><i class="fas fa-save"></i> Save Photo</button>
              @if ($user->avatar)
                <form method="POST" action="{{ route('profile.remove-avatar') }}" class="prof-avatar-remove">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-trash"></i> Remove Photo</button>
                </form>
              @endif
            </form>
          </aside>

          <!-- Details -->
          <div class="prof-main">

            <section class="prof-card">
              <h2 class="prof-card-title"><i class="fas fa-user-edit"></i> About You</h2>
              <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                  <label for="name">Full Name</label>
                  <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" />
                  @error('name')
                    <span class="form-error">{{ $message }}</span>
                  @enderror
                </div>

                <div class="form-group">
                  <label for="bio">Bio</label>
                  <textarea id="bio" name="bio" rows="3" placeholder="A few words about you both..." class="form-control @error('bio') is-invalid @enderror">{{ old('bio', $user->bio) }}</textarea>
                  @error('bio')
                    <span class="form-error">{{ $message }}</span>
                  @enderror
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label for="partner_name">Partner's Name</label>
                    <input type="text" id="partner_name" name="partner_name" value="{{ old('partner_name', $user->partner_name) }}" class="form-control @error('partner_name') is-invalid @enderror" />
                    @error('partner_name')
                      <span class="form-error">{{ $message }}</span>
                    @enderror
                  </div>
                  <div class="form-group">
                    <label for="relationship_date">Anniversary</label>
                    <input type="date" id="relationship_date" name="relationship_date" value="{{ old('relationship_date', $user->relationship_date?->format('Y-m-d')) }}" class="form-control @error('relationship_date') is-invalid @enderror" />
                    @error('relationship_date')
                      <span class="form-error">{{ $message }}</span>
                    @enderror
                  </div>
                </div>

                <div class="form-group">
                  <label for="location">Where We Met / Live</label>
                  <input type="text" id="location" name="location" value="{{ old('location', $user->location) }}" placeholder="e.g. Paris, France" class="form-control @error('location') is-invalid @enderror" />
                  @error('location')
                    <span class="form-error">{{ $message }}</span>
                  @enderror
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Profile</button>
              </form>
            </section>

            <section class="prof-card">
              <h2 class="prof-card-title"><i class="fas fa-lock"></i> Change Password</h2>
              <form method="POST" action="{{ route('profile.update-password') }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                  <label for="current_password">Current Password</label>
                  <input type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" />
                  @error('current_password')
                    <span class="form-error">{{ $message }}</span>
                  @enderror
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" />
                    @error('password')
                      <span class="form-error">{{ $message }}</span>
                    @enderror
                  </div>
                  <div class="form-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" />
                  </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Update Password</button>
              </form>
            </section>

          </div>
        </div>

      </div>
    </main>

  </div>

  <div class="toast-container" id="toast-container" aria-live="polite"></div>

  <script src="{{ asset('js/main.js') }}"></script>
  <script src="{{ asset('js/dashboard.js') }}"></script>
  <script src="{{ asset('js/profile.js') }}"></script>
</body>
</html>
