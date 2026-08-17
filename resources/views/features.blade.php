<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="{{ assetv('css/base.css') }}">
  <title>Memorify - Every Memory Matters</title>
  <meta name="description" content="Discover all the features Memorify offers - photo galleries, photobox collections, digital love letters, and memory timelines." />
  <meta property="og:title" content="Features - Memorify" />
  <meta property="og:description" content="Discover all the features Memorify offers for preserving your memories." />
  <meta property="og:type" content="website" />
  <link rel="stylesheet" href="{{ assetv('css/feature.css') }}">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
</head>
<body>

  <!-- Navigation -->
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
          <a href="{{ url('/features') }}" class="nav-link active">Features</a>
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

  <!-- Hero Section -->
  <section class="hero" id="home">
    <div class="hero-container">
      <div class="hero-content reveal" data-gsap-reveal>
        <div class="hero-badge">
          <i class="far fa-heart"></i>
          <span>Your memories, beautifully organized</span>
        </div>
        <h1 class="hero-title">
          Every Memory<br />Has a <span class="highlight">Story</span>
        </h1>
        <p class="hero-description">
          Store photos, photobox, love letters, and special moments in one
          safe and beautiful place. Cherish it today, treasure it forever.
        </p>
        <div class="hero-buttons">
          <a href="{{ url('/register') }}" class="btn btn-primary btn-lg">
            Get Started <i class="fas fa-arrow-right"></i>
          </a>
          <a href="#showcase" class="btn btn-outline btn-lg">
            Explore Showcase <i class="fas fa-arrow-right"></i>
          </a>
        </div>
        <div class="hero-users">
          <div class="user-avatars">
            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop&crop=face" alt="User 1" />
            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=face" alt="User 2" />
            <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop&crop=face" alt="User 3" />
            <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop&crop=face" alt="User 4" />
          </div>
          <p>Join thousands of people who are preserving their memories <i class="fas fa-heart"></i></p>
        </div>
      </div>
      <div class="hero-visual reveal reveal-delay-2" data-gsap-reveal>
        <!-- Dashboard Preview -->
        <div class="dashboard-preview">
          <div class="dashboard-header">
            <div class="dashboard-logo">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="currentColor" />
              </svg>
              <span>Memorify</span>
            </div>
            <div class="dashboard-actions">
              <i class="far fa-bell"></i>
              <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=40&h=40&fit=crop&crop=face" alt="Profile" class="profile-img" />
            </div>
          </div>
          <div class="dashboard-body">
            <div class="sidebar">
              <div class="sidebar-item active"><i class="fas fa-home"></i><span>Home</span></div>
              <div class="sidebar-item"><i class="far fa-images"></i><span>Gallery</span></div>
              <div class="sidebar-item"><i class="far fa-clock"></i><span>Timeline</span></div>
              <div class="sidebar-item"><i class="far fa-envelope"></i><span>Letters</span></div>
              <div class="sidebar-item"><i class="far fa-image"></i><span>Photobox</span></div>
              <div class="sidebar-item"><i class="fas fa-music"></i><span>Music</span></div>
              <div class="sidebar-item"><i class="far fa-calendar"></i><span>Calendar</span></div>
              <div class="sidebar-item"><i class="fas fa-cog"></i><span>Settings</span></div>
            </div>
            <div class="main-content">
              <div class="welcome-section">
                <div>
                  <h3>Good morning, Amanda <span class="wave">&#x1F44B;</span></h3>
                  <p>What memory will you cherish today?</p>
                </div>
                <button class="btn-add-memory"><i class="fas fa-plus"></i> Add Memory</button>
              </div>
              <div class="journey-card">
                <div class="journey-header">
                  <i class="fas fa-heart"></i>
                  <span>Our Journey</span>
                </div>
                <p class="journey-label">Together for</p>
                <div class="journey-time">
                  <span class="time-unit">1 <small>Year</small></span>
                  <span class="time-unit">4 <small>Months</small></span>
                  <span class="time-unit">12 <small>Days</small></span>
                </div>
                <div class="journey-progress"><div class="progress-bar"></div></div>
                <div class="journey-hearts">
                  <i class="fas fa-heart"></i>
                  <i class="fas fa-heart"></i>
                </div>
              </div>
              <div class="recent-memories">
                <h4>Recent Memories</h4>
                <div class="memory-thumbnails">
                  <div class="memory-thumb"><img src="https://images.unsplash.com/photo-1516589178581-6cd7833ae3b2?w=120&h=120&fit=crop" alt="Memory 1" /></div>
                  <div class="memory-thumb"><img src="https://images.unsplash.com/photo-1522673607200-164d1b6ce486?w=120&h=120&fit=crop" alt="Memory 2" /></div>
                  <div class="memory-thumb"><img src="https://images.unsplash.com/photo-1519741497674-611481863552?w=120&h=120&fit=crop" alt="Memory 3" /></div>
                  <div class="memory-thumb"><img src="https://images.unsplash.com/photo-1529634597503-139d3726fed5?w=120&h=120&fit=crop" alt="Memory 4" /></div>
                  <button class="memory-nav"><i class="fas fa-chevron-right"></i></button>
                </div>
              </div>
              <div class="stats-grid">
                <div class="stat-card gallery-card">
                  <div class="stat-icon"><i class="fas fa-images"></i></div>
                  <div class="stat-info"><span class="stat-name">Gallery</span><span class="stat-count">231 Photos</span></div>
                  <div class="stat-preview">
                    <img src="https://images.unsplash.com/photo-1516589178581-6cd7833ae3b2?w=60&h=60&fit=crop" alt="" />
                    <img src="https://images.unsplash.com/photo-1522673607200-164d1b6ce486?w=60&h=60&fit=crop" alt="" />
                  </div>
                </div>
                <div class="stat-card photobox-card">
                  <div class="stat-icon"><i class="fas fa-box-open"></i></div>
                  <div class="stat-info"><span class="stat-name">Photobox</span><span class="stat-count">48 Collections</span></div>
                  <div class="stat-preview">
                    <img src="https://images.unsplash.com/photo-1519741497674-611481863552?w=60&h=60&fit=crop" alt="" />
                    <img src="https://images.unsplash.com/photo-1529634597503-139d3726fed5?w=60&h=60&fit=crop" alt="" />
                  </div>
                </div>
                <div class="stat-card letters-card">
                  <div class="stat-icon"><i class="far fa-envelope"></i></div>
                  <div class="stat-info"><span class="stat-name">Love Letters</span><span class="stat-count">12 Letters</span></div>
                  <div class="stat-preview">
                    <img src="https://images.unsplash.com/photo-1516589178581-6cd7833ae3b2?w=60&h=60&fit=crop" alt="" />
                    <img src="https://images.unsplash.com/photo-1522673607200-164d1b6ce486?w=60&h=60&fit=crop" alt="" />
                  </div>
                </div>
                <div class="stat-card timeline-card">
                  <div class="stat-icon"><i class="far fa-clock"></i></div>
                  <div class="stat-info"><span class="stat-name">Timeline</span><span class="stat-count">19 Moments</span></div>
                  <div class="stat-preview">
                    <img src="https://images.unsplash.com/photo-1519741497674-611481863552?w=60&h=60&fit=crop" alt="" />
                    <img src="https://images.unsplash.com/photo-1529634597503-139d3726fed5?w=60&h=60&fit=crop" alt="" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Floating Photos -->
        <div class="floating-photo photo-1">
          <img src="https://images.unsplash.com/photo-1519741497674-611481863552?w=200&h=260&fit=crop" alt="Couple photo" />
        </div>
        <div class="floating-photo photo-2">
          <img src="https://images.unsplash.com/photo-1529634597503-139d3726fed5?w=200&h=260&fit=crop" alt="Wedding photo" />
        </div>
        <div class="floating-heart">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
          </svg>
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section class="features" id="features" aria-label="Features section">
    <div class="container">
      <div class="section-header reveal" data-gsap-reveal>
        <span class="section-label">FEATURES</span>
        <h2 class="section-title">Everything You Need to<br />Preserve Your Memories</h2>
      </div>
      <div class="features-grid">
        <div class="feature-card reveal reveal-delay-1" data-gsap-reveal>
          <div class="feature-icon icon-gallery">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" />
              <circle cx="8.5" cy="8.5" r="1.5" />
              <path d="M21 15l-5-5L5 21" />
            </svg>
          </div>
          <h3>Photo Gallery</h3>
          <p>Store your favorite photos in high quality. Organized and easy to find.</p>
        </div>
        <div class="feature-card reveal reveal-delay-2" data-gsap-reveal>
          <div class="feature-icon icon-photobox">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="7" height="7" rx="1" />
              <rect x="14" y="3" width="7" height="7" rx="1" />
              <rect x="3" y="14" width="7" height="7" rx="1" />
              <rect x="14" y="14" width="7" height="7" rx="1" />
            </svg>
          </div>
          <h3>Photobox Collection</h3>
          <p>Collect and organize all your photobox memories by date and occasion.</p>
        </div>
        <div class="feature-card reveal reveal-delay-3" data-gsap-reveal>
          <div class="feature-icon icon-letters">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
              <polyline points="22,6 12,13 2,6" />
            </svg>
          </div>
          <h3>Digital Love Letters</h3>
          <p>Write and save your letters for each other. Revisit sweet words anytime.</p>
        </div>
        <div class="feature-card reveal reveal-delay-4" data-gsap-reveal>
          <div class="feature-icon icon-timeline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
              <line x1="16" y1="2" x2="16" y2="6" />
              <line x1="8" y1="2" x2="8" y2="6" />
              <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
          </div>
          <h3>Memory Timeline</h3>
          <p>See your beautiful journey together, from the first moment until now.</p>
        </div>
      </div>
      <div class="features-cta reveal" data-gsap-reveal>
        <a href="#features" class="link-arrow">
          See All Features <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>
  </section>

  <!-- Showcase Section -->
  <section class="showcase" id="showcase" aria-label="Showcase section">
    <div class="container">
      <div class="section-header reveal" data-gsap-reveal>
        <span class="section-label">SHOWCASE</span>
        <h2 class="section-title">See How Memories Come Together</h2>
      </div>
      <div class="showcase-tabs reveal reveal-delay-1" data-gsap-reveal>
        <button class="tab-btn active" data-tab="gallery">Gallery</button>
        <button class="tab-btn" data-tab="timeline">Timeline</button>
        <button class="tab-btn" data-tab="photobox">Photobox</button>
        <button class="tab-btn" data-tab="letters">Letters</button>
      </div>
      <div class="showcase-content reveal reveal-delay-2" data-gsap-reveal>
        <!-- Gallery Tab -->
        <div class="tab-panel active" id="gallery-panel">
          <div class="gallery-grid">
            <div class="gallery-item large"><img src="https://images.unsplash.com/photo-1516589178581-6cd7833ae3b2?w=400&h=300&fit=crop" alt="Couple by the sea" /></div>
            <div class="gallery-item large"><img src="https://images.unsplash.com/photo-1522673607200-164d1b6ce486?w=400&h=300&fit=crop" alt="Couple with flowers" /></div>
            <div class="gallery-item"><img src="https://images.unsplash.com/photo-1519741497674-611481863552?w=200&h=200&fit=crop" alt="Wedding" /></div>
            <div class="gallery-item"><img src="https://images.unsplash.com/photo-1529634597503-139d3726fed5?w=200&h=200&fit=crop" alt="Couple" /></div>
            <div class="gallery-item"><img src="https://images.unsplash.com/photo-1516589178581-6cd7833ae3b2?w=200&h=200&fit=crop" alt="Memory" /></div>
          </div>
          <a href="{{ url('/register') }}" class="btn btn-sm">View Gallery <i class="fas fa-arrow-right"></i></a>
        </div>
        <!-- Timeline Tab -->
        <div class="tab-panel" id="timeline-panel">
          <div class="timeline">
            <div class="timeline-item">
              <div class="timeline-dot"><i class="fas fa-heart"></i></div>
              <div class="timeline-content">
                <h4>First Meet</h4>
                <span class="timeline-date">12 March 2022</span>
              </div>
            </div>
            <div class="timeline-item">
              <div class="timeline-dot"><i class="fas fa-heart"></i></div>
              <div class="timeline-content">
                <h4>First Date</h4>
                <span class="timeline-date">02 April 2022</span>
              </div>
            </div>
            <div class="timeline-item">
              <div class="timeline-dot"><i class="fas fa-heart"></i></div>
              <div class="timeline-content">
                <h4>First Trip</h4>
                <span class="timeline-date">21 August 2022</span>
              </div>
            </div>
            <div class="timeline-item">
              <div class="timeline-dot"><i class="fas fa-heart"></i></div>
              <div class="timeline-content">
                <h4>Anniversary</h4>
                <span class="timeline-date">12 March 2023</span>
              </div>
            </div>
          </div>
          <a href="{{ url('/register') }}" class="btn btn-sm">View Timeline <i class="fas fa-arrow-right"></i></a>
        </div>
        <!-- Photobox Tab -->
        <div class="tab-panel" id="photobox-panel">
          <div class="photobox-grid">
            <div class="photobox-strip">
              <div class="strip-photo"><img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop&crop=face" alt="" /></div>
              <div class="strip-photo"><img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=face" alt="" /></div>
              <div class="strip-photo"><img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop&crop=face" alt="" /></div>
              <div class="strip-photo"><img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop&crop=face" alt="" /></div>
            </div>
            <div class="photobox-strip">
              <div class="strip-photo"><img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop&crop=face" alt="" /></div>
              <div class="strip-photo"><img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=face" alt="" /></div>
              <div class="strip-photo"><img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop&crop=face" alt="" /></div>
              <div class="strip-photo"><img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop&crop=face" alt="" /></div>
            </div>
          </div>
          <a href="{{ url('/register') }}" class="btn btn-sm">View Photobox <i class="fas fa-arrow-right"></i></a>
        </div>
        <!-- Letters Tab -->
        <div class="tab-panel" id="letters-panel">
          <div class="letter-card">
            <div class="letter-header">
              <span>For you</span>
              <i class="fas fa-heart"></i>
            </div>
            <div class="letter-body">
              <p>Thank you for being my favorite part of every day.</p>
              <p>I love our little moments and big adventures.</p>
              <p class="letter-sign">Forever yours,<br />A</p>
            </div>
          </div>
          <a href="{{ url('/register') }}" class="btn btn-sm">Read More <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="cta-section">
    <div class="container">
      <div class="cta-card reveal" data-gsap-reveal>
        <div class="cta-visual">
          <div class="calendar-illustration">
            <div class="calendar-top">
              <div class="calendar-rings">
                <div class="ring"></div>
                <div class="ring"></div>
                <div class="ring"></div>
                <div class="ring"></div>
                <div class="ring"></div>
                <div class="ring"></div>
              </div>
            </div>
            <div class="calendar-body">
              <div class="calendar-photo">
                <img src="https://images.unsplash.com/photo-1516589178581-6cd7833ae3b2?w=200&h=150&fit=crop" alt="Couple photo" />
              </div>
              <div class="calendar-date">
                <span class="month">FEB</span>
                <span class="day">14</span>
              </div>
            </div>
          </div>
          <div class="pen-illustration"></div>
        </div>
        <div class="cta-content">
          <h2>
            Start Preserving Your<br />Memories <span class="highlight">Today</span>
          </h2>
          <p>Create your private memory archive and keep every precious moment safe for years to come.</p>
          <a href="{{ url('/register') }}" class="btn btn-white btn-lg">
            Create Your Archive <i class="fas fa-arrow-right"></i>
          </a>
          <p class="cta-note">It's free and easy to get started!</p>
        </div>
        <div class="cta-hearts">
          <div class="cta-heart heart-1"><i class="fas fa-heart"></i></div>
          <div class="cta-heart heart-2"><i class="fas fa-heart"></i></div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <a href="{{ url('/') }}" class="logo">
            <div class="logo-icon" style="width:34px;height:34px;font-size:14px;"><i class="fas fa-heart"></i></div>
            <div class="logo-text">
              <div class="brand" style="font-size:18px">Memorify</div>
            </div>
          </a>
          <p class="footer-brand-tagline">Every Memory Matters.<br>Preserve your most precious moments forever.</p>
        </div>
        <div class="footer-col">
          <h4>Product</h4>
          <ul>
            <li><a href="#features">Features</a></li>
            <li><a href="#showcase">Showcase</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Company</h4>
          <ul>
            <li><a href="{{ url('/about') }}">About Us</a></li>
            <li><a href="{{ url('/contact') }}">Contact</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Support</h4>
          <ul>
            <li><a href="{{ url('/contact') }}">Help Center</a></li>
          </ul>
        </div>
      </div>
      </div>
      <div class="footer-bottom">&copy; 2025 Memorify. All rights reserved.</div>
    </div>
  </footer>

  @vite('resources/js/memorify-animations.js')
  @vite('resources/js/features-animations.js')
  <script src="{{ assetv('js/main.js') }}"></script>
  <script src="{{ assetv('js/features.js') }}"></script>
</body>
</html>
