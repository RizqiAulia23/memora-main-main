document.addEventListener('DOMContentLoaded', function () {

  /* ============================
     Mobile menu toggle
     ============================ */
  var mobileMenuBtn = document.querySelector('.mobile-menu-btn');
  var navLinks = document.querySelector('.nav-links');

  if (mobileMenuBtn && navLinks) {
    mobileMenuBtn.addEventListener('click', function () {
      navLinks.classList.toggle('active');
      var isOpen = navLinks.classList.contains('active');
      mobileMenuBtn.innerHTML = isOpen
        ? '<i class="fas fa-times"></i>'
        : '<i class="fas fa-bars"></i>';
      mobileMenuBtn.setAttribute('aria-expanded', isOpen);
    });

    navLinks.querySelectorAll('.nav-link').forEach(function (link) {
      link.addEventListener('click', function () {
        navLinks.classList.remove('active');
        mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
        mobileMenuBtn.setAttribute('aria-expanded', false);
      });
    });
  }

  /* ============================
     Sticky header scroll state
     ============================ */
  var header = document.getElementById('site-header');
  if (header) {
    var scrollThreshold = 40;
    function updateHeaderScroll() {
      if (window.scrollY > scrollThreshold) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    }
    updateHeaderScroll();
    window.addEventListener('scroll', updateHeaderScroll, { passive: true });
  }

  /* ============================
     Animated counters
     ============================ */
  var counterElements = document.querySelectorAll('[data-count]');
  if (counterElements.length && 'IntersectionObserver' in window) {
    var counterObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          counterObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });

    counterElements.forEach(function (el) {
      counterObserver.observe(el);
    });
  }

  function animateCounter(el) {
    var target = parseFloat(el.getAttribute('data-count'));
    var suffix = el.getAttribute('data-suffix') || '';
    var prefix = el.getAttribute('data-prefix') || '';
    var decimal = parseInt(el.getAttribute('data-decimal'), 10) || 0;
    var duration = 1800;
    var startTime = null;

    function step(timestamp) {
      if (!startTime) startTime = timestamp;
      var progress = Math.min((timestamp - startTime) / duration, 1);
      var eased = 1 - Math.pow(1 - progress, 4);
      var current = eased * target;
      el.textContent = prefix + (decimal > 0 ? current.toFixed(decimal) : Math.floor(current)) + suffix;
      if (progress < 1) {
        requestAnimationFrame(step);
      }
    }

    requestAnimationFrame(step);
  }

  /* ============================
     Form submit feedback
     ============================ */
  document.querySelectorAll('form[data-submit-feedback]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (e.defaultPrevented) return;
      var btn = e.submitter || form.querySelector('button[type="submit"]');
      if (!btn || btn.disabled) return;
      btn.disabled = true;
      btn.classList.add('is-submitting');
      form.setAttribute('aria-busy', 'true');
    });
  });

  /* ============================
     Smooth scroll for anchor links
     ============================ */
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      var href = this.getAttribute('href');
      if (href === '#' || href.length < 2) return;
      var target = document.querySelector(href);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });
});
