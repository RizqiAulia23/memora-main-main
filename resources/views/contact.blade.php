<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contact Us - Memorify</title>
  <meta name="description" content="Get in touch with Memorify. We'd love to hear from you about our memory preservation platform." />
  <meta property="og:title" content="Contact Us - Memorify" />
  <meta property="og:description" content="Get in touch with Memorify. We'd love to hear from you." />
  <meta property="og:type" content="website" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="{{ assetv('css/base.css') }}">
  <link rel="stylesheet" href="{{ assetv('css/contact.css') }}">
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
        <div class="nav-links" id="nav-links">
          <a href="{{ url('/') }}" class="nav-link">Home</a>
          <a href="{{ url('/features') }}" class="nav-link">Features</a>
          <a href="{{ url('/about') }}" class="nav-link">About</a>
          <a href="{{ url('/contact') }}" class="nav-link active">Contact</a>
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
  <section class="contact-hero">
    <div class="container">
      <span class="eyebrow reveal" data-gsap-reveal><i class="fas fa-heart"></i> CONTACT US</span>
      <h1 class="reveal reveal-delay-1" data-gsap-reveal>We'd Love to Hear<br>From <span class="accent">You</span></h1>
      <p class="reveal reveal-delay-2" data-gsap-reveal>Have a question, feedback, or just want to say hello? We're here to help and would love to connect with you.</p>
    </div>
  </section>

  <!-- Contact Section -->
  <section class="contact-section">
    <div class="container">
      <div class="contact-grid">

        <!-- Contact Info -->
        <div class="contact-info reveal reveal-delay-1" data-gsap-reveal>
          <h2>Get in Touch</h2>
          <p class="info-subtitle">You can also reach us through the following channels.</p>

          <div class="info-cards">
            <div class="info-card" data-gsap-reveal>
              <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
              <div class="info-content">
                <h4>Address</h4>
                <p>123 Memory Lane<br>Jakarta, Indonesia</p>
              </div>
            </div>
            <div class="info-card" data-gsap-reveal>
              <div class="info-icon"><i class="fas fa-envelope"></i></div>
              <div class="info-content">
                <h4>Email</h4>
                <p>hello@memorify.com</p>
              </div>
            </div>
            <div class="info-card" data-gsap-reveal>
              <div class="info-icon"><i class="fas fa-phone-alt"></i></div>
              <div class="info-content">
                <h4>Phone</h4>
                <p>+62 123 456 789</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- CTA Banner -->
  <section class="cta-banner" id="start">
    <div class="container">
      <div class="cta-banner-inner reveal" data-gsap-reveal>
        <div>
          <h2>Start Preserving Your Memories Today</h2>
          <p>Create your private memory archive and keep every precious moment safe for years to come.</p>
        </div>
        <a href="{{ url('/register') }}" class="btn btn-white" aria-label="Get started now">Get Started &rarr;</a>
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
  @vite('resources/js/contact-animations.js')
  <script src="{{ assetv('js/main.js') }}"></script>
</body>
</html>
