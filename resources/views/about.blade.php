<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us - Memorify</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="{{ assetv('css/base.css') }}">
  <link rel="stylesheet" href="{{ assetv('css/about.css') }}">
  <meta name="description" content="Learn about Memorify - the memory preservation platform built for couples and families to store photos, love letters, and special moments." />
  <meta property="og:title" content="About Us - Memorify" />
  <meta property="og:description" content="Learn about Memorify - the memory preservation platform built for couples and families." />
  <meta property="og:type" content="website" />
</head>
<body>

  <!-- HEADER -->
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
        <div class="nav-links" id="nav-links">
          <a href="{{ url('/') }}" class="nav-link">Home</a>
          <a href="{{ url('/features') }}" class="nav-link">Features</a>
          <a href="{{ url('/about') }}" class="nav-link active">About</a>
          <a href="{{ url('/contact') }}" class="nav-link">Contact</a>
        </div>
        <div class="nav-buttons">
          <a href="{{ url('/login') }}" class="btn btn-outline">Login</a>
          <a href="{{ url('/register') }}" class="btn btn-primary">Get Started</a>
        </div>
        <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle navigation menu"><i class="fas fa-bars"></i></button>
      </div>
    </nav>
  </header>

  <!-- HERO -->
  <section class="about-hero">
    <div class="container">
      <span class="eyebrow reveal" data-gsap-reveal>&#10084; About Memorify</span>
      <h1 class="reveal reveal-delay-1" data-gsap-reveal>Preserving Love,<br>One <span class="accent">Story</span> at a Time</h1>
      <p class="reveal reveal-delay-2" data-gsap-reveal>We believe every memory deserves a beautiful home. Memorify was built to help couples and families keep their precious moments safe, organized, and easy to revisit forever.</p>
    </div>
  </section>

  <!-- STORY -->
  <section class="story">
    <div class="container">
      <div class="story-grid">
        <div class="story-images reveal" data-gsap-reveal>
          <img class="main-img" src="https://images.unsplash.com/photo-1518199266791-5375a83190b7?w=700&q=70" alt="Couple sharing a story together">
          <div class="float-card fc1">
            <div>
              <b>5+</b>
              <span>Years of Building</span>
            </div>
          </div>
        </div>
        <div class="story-copy reveal reveal-delay-1" data-gsap-reveal>
          <div class="section-eyebrow">OUR STORY</div>
          <h2>Why We Built Memorify</h2>
          <p>It started with a simple problem: photos scattered across phones, letters lost in old notebooks, and memories fading with time. We wanted one safe, beautiful place to keep it all.</p>
          <p>Today, Memorify helps thousands of couples and families store their photos, letters, and milestones in a single archive that grows more meaningful every year.</p>
          <a href="{{ url('/register') }}" class="btn btn-primary">Start Your Archive &rarr;</a>
        </div>
      </div>
    </div>
  </section>

  <!-- STATS -->
  <section class="stats">
    <div class="container">
      <div class="stats-grid">
        <div class="stat-card reveal reveal-delay-1" data-gsap-reveal>
          <b data-count="50" data-suffix="K+">0</b>
          <span>Active Users</span>
        </div>
        <div class="stat-card reveal reveal-delay-2" data-gsap-reveal>
          <b data-count="2" data-suffix="M+">0</b>
          <span>Memories Saved</span>
        </div>
        <div class="stat-card reveal reveal-delay-3" data-gsap-reveal>
          <b data-count="120" data-suffix="+">0</b>
          <span>Countries Reached</span>
        </div>
        <div class="stat-card reveal reveal-delay-4" data-gsap-reveal>
          <b data-count="4.9" data-suffix="/5" data-decimal="true">0</b>
          <span>Average Rating</span>
        </div>
      </div>
    </div>
  </section>

  <!-- VALUES -->
  <section class="values">
    <div class="container">
      <div class="section-eyebrow reveal" data-gsap-reveal>OUR VALUES</div>
      <h2 class="reveal reveal-delay-1" data-gsap-reveal>What We Stand For</h2>
      <div class="value-grid">
        <div class="value-card reveal reveal-delay-1" data-gsap-reveal>
          <div class="value-icon pink">&#128274;</div>
          <h3>Privacy First</h3>
          <p>Your memories are yours alone. We keep every photo, letter, and moment private and secure.</p>
        </div>
        <div class="value-card reveal reveal-delay-2" data-gsap-reveal>
          <div class="value-icon peach">&#10024;</div>
          <h3>Simple &amp; Beautiful</h3>
          <p>We design every feature to feel warm and effortless, so preserving memories never feels like a chore.</p>
        </div>
        <div class="value-card reveal reveal-delay-3" data-gsap-reveal>
          <div class="value-icon purple">&#10084;</div>
          <h3>Made With Love</h3>
          <p>Every detail is crafted with care, because we believe your story deserves to be told beautifully.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- TEAM -->
  <section class="team">
    <div class="container">
      <div class="section-eyebrow reveal" data-gsap-reveal>OUR TEAM</div>
      <h2 class="reveal reveal-delay-1" data-gsap-reveal>The People Behind Memorify</h2>
      <div class="team-grid">
        <div class="team-card reveal reveal-delay-1" data-gsap-reveal>
          <img src="https://i.pravatar.cc/150?img=32" alt="Amanda Rey portrait">
          <h4>Amanda Rey</h4>
          <span>Founder &amp; CEO</span>
        </div>
        <div class="team-card reveal reveal-delay-2" data-gsap-reveal>
          <img src="https://i.pravatar.cc/150?img=12" alt="David Lin portrait">
          <h4>David Lin</h4>
          <span>Product Design</span>
        </div>
        <div class="team-card reveal reveal-delay-3" data-gsap-reveal>
          <img src="https://i.pravatar.cc/150?img=45" alt="Sarah Putri portrait">
          <h4>Sarah Putri</h4>
          <span>Engineering Lead</span>
        </div>
        <div class="team-card reveal reveal-delay-4" data-gsap-reveal>
          <img src="https://i.pravatar.cc/150?img=47" alt="Michael Tan portrait">
          <h4>Michael Tan</h4>
          <span>Customer Care</span>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <div class="about-cta-wrap">
    <div class="about-cta-banner reveal" data-gsap-reveal>
      <div>
        <h2>Join Our Story</h2>
        <p>Start preserving your own memories with Memorify today. It's free.</p>
      </div>
      <a href="{{ url('/register') }}" class="btn btn-white">Get Started &rarr;</a>
    </div>
  </div>

  <!-- FOOTER -->
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
            <li><a href="{{ url('/features') }}">Features</a></li>
            <li><a href="{{ url('/features') }}#showcase">Showcase</a></li>
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
  @vite('resources/js/about-animations.js')
  <script src="{{ assetv('js/main.js') }}"></script>
</body>
</html>
