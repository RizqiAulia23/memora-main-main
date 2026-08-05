<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login – Memorify</title>
    <meta
      name="description"
      content="Sign in to Memorify and continue preserving your most precious memories together."
    />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link
      href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    />
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
  </head>
  <body>
    <!-- ===== HEADER ===== -->
    <header class="site-header" id="site-header">
      <nav class="navbar" aria-label="Main navigation">
        <div class="nav-container">
          <a href="{{ url('/') }}" class="logo">
            <div class="logo-icon"><i class="fas fa-heart"></i></div>
            <div class="logo-text">
              <div class="brand">Memorify</div>
              <div class="tagline">Every Memory Matters</div>
            </div>
          </a>

          <div class="nav-links">
            <a href="{{ url('/') }}" class="nav-link">Home</a>
            <a href="{{ url('/features') }}" class="nav-link">Features</a>
            <a href="{{ url('/about') }}" class="nav-link">About</a>
            <a href="{{ url('/contact') }}" class="nav-link">Contact</a>
          </div>

          <div class="nav-buttons">
            <a href="{{ url('/login') }}" class="btn btn-outline" aria-label="Login">Login</a>
            <a href="{{ url('/register') }}" class="btn btn-primary" aria-label="Get Started">Get Started</a>
          </div>
          <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle navigation menu"><i class="fas fa-bars"></i></button>
        </div>
      </nav>
    </header>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="login-page">
      <div class="login-card">
        <!-- LEFT PANEL -->
        <div class="panel-left">
          <!-- Floating hearts -->
          <span class="deco-heart h1">&#9825;</span>
          <span class="deco-heart h2">&#9825;</span>
          <span class="deco-heart h3">&#9825;</span>
          <span class="deco-heart h4">&#9829;</span>

          <!-- Polaroids -->
          <div class="polaroids-wrap">
            <div class="polaroid pol-1">
              <img
                src="https://images.unsplash.com/photo-1529634806980-85c3dd6d34ac?w=300&q=80"
                alt="couple with flowers"
              />
              <div class="pol-tape"></div>
            </div>
            <div class="polaroid pol-2">
              <img
                src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&q=80"
                alt="couple at sunset"
              />
              <div class="pol-tape"></div>
            </div>
            <div class="polaroid pol-3">
              <img
                src="https://images.unsplash.com/photo-1518199266791-5375a83190b7?w=300&q=80"
                alt="couple at ferris wheel"
              />
              <div class="pol-tape"></div>
            </div>
          </div>

          <!-- Flowers decoration -->
          <div class="flowers-deco">
            <svg
              viewBox="0 0 120 200"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
              class="flowers-svg"
            >
              <circle cx="20" cy="30" r="6" fill="rgba(255,255,255,0.5)" />
              <circle cx="35" cy="15" r="4" fill="rgba(255,255,255,0.4)" />
              <circle cx="50" cy="40" r="5" fill="rgba(255,255,255,0.45)" />
              <circle cx="15" cy="55" r="3" fill="rgba(255,255,255,0.35)" />
              <circle cx="65" cy="20" r="4" fill="rgba(255,255,255,0.4)" />
              <circle cx="80" cy="50" r="3" fill="rgba(255,255,255,0.3)" />
              <circle cx="30" cy="70" r="5" fill="rgba(255,255,255,0.4)" />
              <circle cx="10" cy="80" r="3" fill="rgba(255,255,255,0.3)" />
              <line
                x1="20"
                y1="36"
                x2="18"
                y2="80"
                stroke="rgba(255,255,255,0.4)"
                stroke-width="1.5"
              />
              <line
                x1="35"
                y1="19"
                x2="22"
                y2="80"
                stroke="rgba(255,255,255,0.35)"
                stroke-width="1.5"
              />
              <line
                x1="50"
                y1="45"
                x2="32"
                y2="80"
                stroke="rgba(255,255,255,0.35)"
                stroke-width="1.5"
              />
              <line
                x1="65"
                y1="24"
                x2="45"
                y2="80"
                stroke="rgba(255,255,255,0.3)"
                stroke-width="1.5"
              />
            </svg>
          </div>

          <!-- Bottom text -->
          <div class="panel-left-footer">
            <span class="heart-label">&#9829; Every Memory Matters</span>
            <h2 class="welcome-title">Welcome Back</h2>
            <p class="welcome-sub">
              Continue preserving your<br />most precious memories<br />together.
            </p>
          </div>
        </div>

        <!-- RIGHT PANEL -->
        <div class="panel-right">
          <div class="form-wrap">
            <span class="eyebrow-badge">&#9829; Welcome Back</span>
            <h1 class="form-title">Login</h1>
            <p class="form-sub">Sign in to continue your journey.</p>

            <form id="login-form" action="{{ route('login') }}" method="POST" novalidate aria-label="Login form">
              @csrf
              @if (session('success'))
                <div class="form-alert form-alert-success">
                  <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
              @endif
              @if ($errors->any())
                <div class="form-alert form-alert-error">
                  <strong>Unable to sign in.</strong>
                  <ul>
                    @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
              @endif
              <!-- Username -->
              <div class="field">
                <div class="input-box">
                  <svg
                    class="field-icon"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  >
                    <circle cx="12" cy="8" r="4" />
                    <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" />
                  </svg>
                  <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Email"
                    autocomplete="email"
                    value="{{ old('email') }}"
                    required
                    />
                </div>
              </div>

              <!-- Password -->
              <div class="field">
                <div class="input-box">
                  <svg
                    class="field-icon"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  >
                    <rect x="3" y="11" width="18" height="11" rx="2" />
                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                  </svg>
                  <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Password"
                    autocomplete="current-password"
                    required
                  />
                  <button
                    type="button"
                    class="toggle-pass"
                    id="toggle-pass"
                    onclick="togglePassword()"
                    aria-label="Toggle password visibility"
                  >
                    <svg
                      id="eye-icon"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    >
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                      <circle cx="12" cy="12" r="3" />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Remember + Forgot -->
              <div class="row-meta">
                <label class="remember-label">
                  <input type="checkbox" id="remember" name="remember" />
                  <span class="remember-text">Remember me</span>
                </label>
                <a href="#" class="forgot-link" id="forgot-link"
                  >Forgot password?</a
                >
              </div>

              <!-- Submit -->
              <button type="submit" class="btn-login-submit" id="btn-submit">
                Login
                <svg viewBox="0 0 24 24" fill="currentColor">
                  <path
                    d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"
                  />
                </svg>
              </button>

              <!-- Divider -->
              <div class="divider"><span>or</span></div>

              <!-- Google -->
              <button type="button" class="btn-google" id="btn-google">
                <svg viewBox="0 0 24 24" class="google-icon">
                  <path
                    fill="#4285F4"
                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                  />
                  <path
                    fill="#34A853"
                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                  />
                  <path
                    fill="#FBBC05"
                    d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                  />
                  <path
                    fill="#EA4335"
                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                  />
                </svg>
                Continue with Google
              </button>

              <!-- Sign up note -->
              <p class="signup-note">
                Don't have an account?
                <a href="{{ url('/register') }}" id="link-signup">Sign Up</a>
              </p>
            </form>
          </div>
        </div>
      </div>
    </main>

    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/auth.js') }}"></script>
  </body>
</html>
