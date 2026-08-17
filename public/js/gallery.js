document.addEventListener('DOMContentLoaded', function () {
  var grid = document.querySelector('[data-gallery-grid]');
  var lightbox = document.getElementById('gal-lightbox');
  var lightboxImg = lightbox ? lightbox.querySelector('[data-lightbox-img]') : null;
  var lightboxTitle = lightbox ? lightbox.querySelector('[data-lightbox-title]') : null;
  var lightboxDate = lightbox ? lightbox.querySelector('[data-lightbox-date]') : null;
  var lightboxLink = lightbox ? lightbox.querySelector('[data-lightbox-link]') : null;

  var items = [];
  var current = 0;

  function collectItems() {
    items = Array.prototype.slice.call(document.querySelectorAll('[data-gallery-item]'));
  }

  function applyItem(item) {
    lightboxImg.src = item.getAttribute('data-src');
    lightboxImg.alt = item.getAttribute('data-title') || '';
    lightboxTitle.textContent = item.getAttribute('data-title') || '';
    lightboxDate.textContent = item.getAttribute('data-date') || '';
    lightboxLink.href = item.getAttribute('data-url') || '#';
    lightboxImg.classList.remove('gal-img-swap');
    lightboxImg.classList.remove('gal-img-error');
    lightbox.classList.add('gal-loading');
    void lightboxImg.offsetWidth;
    lightboxImg.classList.add('gal-img-swap');
  }

  function onImgReady() {
    lightbox.classList.remove('gal-loading');
  }

  function onImgError() {
    lightbox.classList.remove('gal-loading');
    lightboxImg.classList.add('gal-img-error');
    var figure = lightbox.querySelector('.gal-lightbox-figure');
    if (figure) figure.classList.add('gal-img-error-msg');
  }

  function openLightbox(index) {
    collectItems();
    if (!items.length) return;
    current = index;
    applyItem(items[current]);
    lightbox.hidden = false;
    document.body.style.overflow = 'hidden';
    var closeBtn = lightbox.querySelector('[data-lightbox-close]');
    if (closeBtn) closeBtn.focus();
  }

  function closeLightbox() {
    lightbox.hidden = true;
    document.body.style.overflow = '';
    if (grid) grid.focus({ preventScroll: true });
  }

  function step(delta) {
    collectItems();
    current = (current + delta + items.length) % items.length;
    applyItem(items[current]);
  }

  if (grid) {
    grid.addEventListener('click', function (e) {
      if (e.target.closest('.gal-download')) return;
      var item = e.target.closest('[data-gallery-item]');
      if (!item) return;
      var index = Array.prototype.indexOf.call(grid.querySelectorAll('[data-gallery-item]'), item);
      openLightbox(index);
    });
    grid.addEventListener('keydown', function (e) {
      if (e.key !== 'Enter' && e.key !== ' ') return;
      if (e.target.closest('.gal-download')) return;
      var item = e.target.closest('[data-gallery-item]');
      if (!item) return;
      e.preventDefault();
      var index = Array.prototype.indexOf.call(grid.querySelectorAll('[data-gallery-item]'), item);
      openLightbox(index);
    });
  }

  if (lightbox) {
    lightboxImg.addEventListener('load', onImgReady);
    lightboxImg.addEventListener('error', onImgError);
    lightbox.querySelector('[data-lightbox-close]').addEventListener('click', closeLightbox);
    lightbox.addEventListener('click', function (e) {
      if (e.target === lightbox) closeLightbox();
    });
    document.addEventListener('keydown', function (e) {
      if (lightbox.hidden) return;
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowLeft') step(-1);
      if (e.key === 'ArrowRight') step(1);
    });

    var prev = lightbox.querySelector('[data-lightbox-prev]');
    var next = lightbox.querySelector('[data-lightbox-next]');
    if (prev) prev.addEventListener('click', function () { step(-1); });
    if (next) next.addEventListener('click', function () { step(1); });
  }

  // Infinite scroll
  var loadEl = document.querySelector('[data-gallery-load]');
  var loading = false;
  var hasMore = loadEl ? true : false;

  if (loadEl) {
    window.addEventListener('scroll', function () {
      if (loading || !hasMore) return;
      var rect = loadEl.getBoundingClientRect();
      if (rect.top < window.innerHeight + 200) {
        loading = true;
        var nextUrl = loadEl.getAttribute('data-next');
        if (!nextUrl) {
          loading = false;
          hasMore = false;
          loadEl.hidden = true;
          return;
        }
        fetch(nextUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then(function (res) {
            if (!res.ok) throw new Error('Request failed');
            return res.json();
          })
          .then(function (data) {
            var before = grid.querySelectorAll(':scope > .gal-item').length;
            grid.insertAdjacentHTML('beforeend', data.html);
            var all = grid.querySelectorAll(':scope > .gal-item');
            var appended = Array.prototype.slice.call(all).slice(before);
            appended.forEach(function (el) {
              el.classList.add('gal-enter');
            });
            if (
              appended.length &&
              window.MemorifyAnimations &&
              window.MemorifyAnimations.gsap &&
              !window.MemorifyAnimations.prefersReducedMotion()
            ) {
              appended.forEach(function (el) {
                el.style.opacity = '0';
                el.style.transform = 'translateY(14px)';
              });
              window.MemorifyAnimations.gsap.to(appended, {
                opacity: 1,
                y: 0,
                duration: 0.45,
                ease: 'power2.out',
                clearProps: 'transform,opacity',
              });
              window.MemorifyAnimations.refreshScrollTriggers();
            }
            loadEl.setAttribute('data-next', data.nextUrl || '');
            hasMore = data.hasMore;
            if (!hasMore) loadEl.hidden = true;
            loading = false;
          })
          .catch(function () {
            loading = false;
            hasMore = false;
            loadEl.hidden = true;
            showToast('Could not load more photos. Please try again.', 'error');
          });
      }
    });
  }
});
