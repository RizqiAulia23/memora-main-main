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

  function openLightbox(index) {
    collectItems();
    if (!items.length) return;
    current = index;
    var item = items[current];
    lightboxImg.src = item.getAttribute('data-src');
    lightboxImg.alt = item.getAttribute('data-title') || '';
    lightboxTitle.textContent = item.getAttribute('data-title') || '';
    lightboxDate.textContent = item.getAttribute('data-date') || '';
    lightboxLink.href = item.getAttribute('data-url') || '#';
    lightbox.hidden = false;
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    lightbox.hidden = true;
    document.body.style.overflow = '';
  }

  function step(delta) {
    collectItems();
    current = (current + delta + items.length) % items.length;
    var item = items[current];
    lightboxImg.src = item.getAttribute('data-src');
    lightboxImg.alt = item.getAttribute('data-title') || '';
    lightboxTitle.textContent = item.getAttribute('data-title') || '';
    lightboxDate.textContent = item.getAttribute('data-date') || '';
    lightboxLink.href = item.getAttribute('data-url') || '#';
  }

  if (grid) {
    grid.addEventListener('click', function (e) {
      var item = e.target.closest('[data-gallery-item]');
      if (!item) return;
      var index = Array.prototype.indexOf.call(grid.querySelectorAll('[data-gallery-item]'), item);
      openLightbox(index);
    });
  }

  if (lightbox) {
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
          hasMore = false;
          loadEl.hidden = true;
          return;
        }
        fetch(nextUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then(function (res) { return res.json(); })
          .then(function (data) {
            grid.insertAdjacentHTML('beforeend', data.html);
            loadEl.setAttribute('data-next', data.nextUrl || '');
            hasMore = data.hasMore;
            if (!hasMore) loadEl.hidden = true;
            loading = false;
          })
          .catch(function () { loading = false; });
      }
    });
  }
});
