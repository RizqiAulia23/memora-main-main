<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Memorify - Every Memory Matters</title>
    <meta name="description" content="Store photos, photobox, love letters, and special moments in one safe and beautiful place. Cherish it today, treasure it forever." />
    <meta property="og:title" content="Memorify - Every Memory Matters" />
    <meta property="og:description" content="Store photos, photobox, love letters, and special moments in one safe and beautiful place." />
    <meta property="og:type" content="website" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="{{ assetv('css/base.css') }}">
    <link rel="stylesheet" href="{{ assetv('css/home.css') }}">
  </head>
  <body>
    <!-- HEADER -->
    <header class="site-header" id="site-header">
      <nav class="navbar" aria-label="Main navigation">
        <div class="nav-container">
          <a href="{{ url('/') }}" class="logo" id="logo">
            <div class="logo-icon"><i class="fas fa-heart"></i></div>
            <div class="logo-text">
              <div class="brand">Memorify</div>
              <div class="tagline">Every Memory Matters</div>
            </div>
          </a>
          <div class="nav-links" id="nav-links">
            <a href="{{ url('/') }}" class="nav-link">Home</a>
            <a href="{{ url('/features') }}" class="nav-link">Features</a>
            <a href="{{ url('/about') }}" class="nav-link">About</a>
            <a href="{{ url('/contact') }}" class="nav-link">Contact</a>
          </div>
          <div class="nav-buttons">
            <a href="{{ url('/login') }}" class="btn btn-outline">Login</a>
            <a href="{{ url('/register') }}" class="btn btn-primary" id="btn-getstarted">Get Started</a>
          </div>
          <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle navigation menu"><i class="fas fa-bars"></i></button>
        </div>
      </nav>
    </header>

    <!-- HERO -->
    <section class="hero" id="hero">
      <span class="float-el" style="top: 12%; left: 7%; font-size: 22px; color: var(--pink-400)">&#9829;</span>
      <span class="float-el" style="top: 65%; left: 4%; font-size: 16px; color: #f472a0; animation-delay: 1.2s">&#9829;</span>
      <span class="float-el" style="top: 30%; left: 48%; font-size: 14px; color: var(--pink-400); animation-delay: 0.6s; opacity: 0.3">&#9829;</span>

      <div class="container">
        <div class="hero-grid">
          <div class="hero-copy">
            <div class="eyebrow">&#9829; Your memories, beautifully organized</div>
            <h1>Every Memory<br />Has <span class="accent">a Story</span></h1>
            <p class="lead">
              Store photos, photobox, love letters, and special moments in one
              safe and beautiful place. Cherish it today, treasure it forever.
            </p>
            <div class="hero-ctas">
              <a href="{{ url('/register') }}" class="btn btn-primary" id="cta-started">Get Started &rarr;</a>
              <a href="{{ url('/features') }}" class="btn btn-outline" id="cta-showcase">Explore Features &rarr;</a>
            </div>
            <div class="avatars">
              <div class="avatar-stack">
                <img src="https://i.pravatar.cc/80?img=32" alt="User avatar" />
                <img src="https://i.pravatar.cc/80?img=45" alt="User avatar" />
                <img src="https://i.pravatar.cc/80?img=12" alt="User avatar" />
                <img src="https://i.pravatar.cc/80?img=47" alt="User avatar" />
              </div>
              <p>Join thousands of people who are preserving their memories &#9829;</p>
            </div>
          </div>

          <div class="hero-visual">
            <div class="dashboard">
              <div class="dash-sidebar">
                <div class="dash-logo">
                  <div class="dlogo-icon">&#9829;</div>
                  Memorify
                </div>
                <div class="dash-nav">
                  <div class="dash-nav-item active"><span class="ni">&#8962;</span> Home</div>
                  <div class="dash-nav-item"><span class="ni">&#9635;</span> Gallery</div>
                  <div class="dash-nav-item"><span class="ni">&#8635;</span> Timeline</div>
                  <div class="dash-nav-item"><span class="ni">&#9993;</span> Letters</div>
                  <div class="dash-nav-item"><span class="ni">&#9635;</span> Photobox</div>
                  <div class="dash-nav-item"><span class="ni">&#9834;</span> Music</div>
                  <div class="dash-nav-item"><span class="ni">&#128197;</span> Calendar</div>
                  <div class="dash-nav-item"><span class="ni">&#9881;</span> Settings</div>
                </div>
              </div>
              <div class="dash-main">
                <div class="dash-top">
                  <div>
                    <h3>Good morning, Amanda &#128075;</h3>
                    <p>What memory will you cherish today?</p>
                  </div>
                  <button class="btn-add">+ Add Memory</button>
                </div>
                <div class="dash-cards">
                  <div class="dash-card">
                    <h4>&#9829; Our Journey</h4>
                    <div class="journey-nums">
                      <b>1</b><span>Year</span> <b>4</b><span>Months</span> <b>12</b><span>Days</span>
                    </div>
                    <div class="progress-bar"><i></i></div>
                  </div>
                  <div class="dash-card">
                    <h4>Recent Memories</h4>
                    <div class="thumb-row">
                      <img src="https://images.unsplash.com/photo-1522673607200-164d1b6ce486?w=100&q=60" alt="Memory thumbnail" />
                      <img src="https://images.unsplash.com/photo-1529634806980-85c3dd6d34ac?w=100&q=60" alt="Memory thumbnail" />
                      <img src="https://images.unsplash.com/photo-1518199266791-5375a83190b7?w=100&q=60" alt="Memory thumbnail" />
                      <img src="https://images.unsplash.com/photo-1516589178581-6cd7833ae3b2?w=100&q=60" alt="Memory thumbnail" />
                    </div>
                  </div>
                </div>
                <div class="dash-tiles">
                  <div class="dash-tile pink">Gallery <p>231 Photos</p></div>
                  <div class="dash-tile purple">Photobox <p>48 Collections</p></div>
                  <div class="dash-tile peach">Love Letters <p>12 Letters</p></div>
                  <div class="dash-tile green">Timeline <p>19 Moments</p></div>
                </div>
              </div>
            </div>
            <div class="polaroid p1">
              <img src="https://images.unsplash.com/photo-1529634806980-85c3dd6d34ac?w=200&q=60" alt="Couple memory" />
            </div>
            <div class="polaroid p2">
              <img src="https://images.unsplash.com/photo-1519741497674-611481863552?w=200&q=60" alt="Wedding memory" />
            </div>
            <span class="float-el" style="top: 42%; left: -14px; font-size: 24px; color: var(--pink-500)">&#9829;</span>
          </div>
        </div>
      </div>
    </section>

    <!-- FEATURES -->
    <section class="features" id="features">
      <div class="container">
        <div class="reveal" data-gsap-reveal>
          <div class="section-label">FEATURES</div>
          <h2 class="section-title">Everything You Need to<br />Preserve Your Memories</h2>
        </div>
        <div class="feature-grid">
          <div class="feature-card reveal reveal-delay-1" id="feat-gallery" data-gsap-reveal>
            <div class="feat-icon pink">&#128247;</div>
            <h3>Photo Gallery</h3>
            <p>Store your favorite photos in high quality. Organized and easy to find.</p>
          </div>
          <div class="feature-card reveal reveal-delay-2" id="feat-photobox" data-gsap-reveal>
            <div class="feat-icon peach">&#127909;</div>
            <h3>Photobox Collection</h3>
            <p>Collect and organize all your photobox memories by date and occasion.</p>
          </div>
          <div class="feature-card reveal reveal-delay-3" id="feat-letters" data-gsap-reveal>
            <div class="feat-icon purple">&#9993;</div>
            <h3>Digital Love Letters</h3>
            <p>Write and save your letters for each other. Revisit sweet words anytime.</p>
          </div>
          <div class="feature-card reveal reveal-delay-4" id="feat-timeline" data-gsap-reveal>
            <div class="feat-icon teal">&#128197;</div>
            <h3>Memory Timeline</h3>
            <p>See your beautiful journey together, from the first moment until now.</p>
          </div>
        </div>
        <div class="see-all-wrap reveal" data-gsap-reveal>
          <a href="{{ url('/features') }}" class="see-all" id="see-all-features">See All Features &rarr;</a>
        </div>
      </div>
    </section>

    <!-- SHOWCASE -->
    <section class="showcase" id="showcase">
      <div class="container">
        <div class="showcase-head reveal" data-gsap-reveal>
          <div class="section-label">SHOWCASE</div>
          <h2 class="section-title" style="margin-bottom: 0">See How Memories Come Together</h2>
        </div>
        <div class="tab-grid">
          <div class="tab-card reveal reveal-delay-1" id="sc-gallery" data-gsap-reveal>
            <span class="tab-pill active">Gallery</span>
            <div class="gallery-mosaic">
              <img class="wide" src="https://images.unsplash.com/photo-1522673607200-164d1b6ce486?w=300&q=60" alt="Gallery preview" />
              <img src="https://images.unsplash.com/photo-1470770841072-f978cf4d019e?w=200&q=60" alt="Gallery preview" />
              <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200&q=60" alt="Gallery preview" />
            </div>
            <a href="{{ url('/features') }}" class="btn-sm tab-action" id="sc-gallery-btn">View Gallery &rarr;</a>
          </div>

          <div class="tab-card reveal reveal-delay-2" id="sc-timeline" data-gsap-reveal>
            <span class="tab-pill inactive">Timeline</span>
            <div class="tl-list">
              <div class="tl-item"><div><b>First Meet</b><span>12 March 2022</span></div></div>
              <div class="tl-item"><div><b>First Date</b><span>02 April 2022</span></div></div>
              <div class="tl-item"><div><b>First Trip</b><span>21 August 2022</span></div></div>
              <div class="tl-item"><div><b>Anniversary</b><span>12 March 2023</span></div></div>
            </div>
            <a href="{{ url('/features') }}" class="btn-sm tab-action" id="sc-timeline-btn">View Timeline &rarr;</a>
          </div>

          <div class="tab-card reveal reveal-delay-3" id="sc-photobox" data-gsap-reveal>
            <span class="tab-pill inactive">Photobox</span>
            <div class="pb-strip">
              <img src="https://images.unsplash.com/photo-1529634806980-85c3dd6d34ac?w=200&q=60" alt="Photobox preview" />
              <img src="https://images.unsplash.com/photo-1521119989659-a83eee488004?w=200&q=60" alt="Photobox preview" />
              <img src="https://images.unsplash.com/photo-1518199266791-5375a83190b7?w=200&q=60" alt="Photobox preview" />
              <img src="https://images.unsplash.com/photo-1516589178581-6cd7833ae3b2?w=200&q=60" alt="Photobox preview" />
            </div>
            <a href="{{ url('/features') }}" class="btn-sm tab-action" id="sc-photobox-btn">View Photobox &rarr;</a>
          </div>

          <div class="tab-card reveal reveal-delay-4" id="sc-letters" data-gsap-reveal>
            <span class="tab-pill inactive">Letters</span>
            <div class="letter-card">
              <span class="l-title">For you &#9829;</span>
              Thank you for being my favorite part of every day. I love our
              little moments and big adventures.
              <div class="l-sign">Forever yours,<br />A</div>
            </div>
            <a href="{{ url('/features') }}" class="btn-sm tab-action" id="sc-letters-btn">Read More &rarr;</a>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA BANNER -->
    <div class="cta-section" id="start">
      <div class="cta-banner reveal" data-gsap-reveal>
        <span class="cta-heart" style="top: 16px; left: 240px; font-size: 24px">&#9829;</span>
        <span class="cta-heart" style="bottom: 18px; right: 260px; font-size: 18px">&#9829;</span>
        <div class="cta-calendar">
          <img src="https://images.unsplash.com/photo-1522673607200-164d1b6ce486?w=200&q=60" alt="Memory preview" />
        </div>
        <div class="cta-copy">
          <h2>Start Preserving Your<br />Memories <em>Today</em></h2>
          <p>Create your private memory archive and keep every precious moment safe for years to come.</p>
        </div>
        <div class="cta-action">
          <a href="{{ url('/register') }}" class="btn btn-white" id="cta-archive">Create Your Archive &rarr;</a>
          <small>It's free and easy to get started!</small>
        </div>
      </div>
    </div>

    <!-- FOOTER -->
    <footer id="footer">
      <div class="container">
        <div class="footer-grid">
          <div class="footer-brand">
            <a href="{{ url('/') }}" class="logo" id="footer-logo">
              <div class="logo-icon" style="width: 34px; height: 34px; font-size: 14px"><i class="fas fa-heart"></i></div>
              <div class="logo-text">
                <div class="brand" style="font-size: 18px">Memorify</div>
              </div>
            </a>
            <p class="footer-brand-tagline">Every Memory Matters.<br />Preserve your most precious moments forever.</p>
          </div>
          <div class="footer-col">
            <h4>Product</h4>
            <ul>
              <li><a href="{{ url('/features') }}">Features</a></li>
              <li><a href="{{ url('/features') }}#showcase">Showcase</a></li>
              <li><a href="{{ url('/register') }}">Get Started</a></li>
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
        <div class="footer-bottom">&copy; 2025 Memorify. All rights reserved.</div>
      </div>
    </footer>
    @vite('resources/js/memorify-animations.js')
    @vite('resources/js/home-animations.js')
    <script src="{{ assetv('js/main.js') }}"></script>
  </body>
</html>
