/* Global toast helper */
function showToast(message, type) {
  var container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    container.id = 'toast-container';
    document.body.appendChild(container);
  }

  var toast = document.createElement('div');
  toast.className = 'toast ' + (type || 'success');
  toast.textContent = message;
  container.appendChild(toast);

  setTimeout(function () {
    toast.classList.add('hide');
    toast.addEventListener('animationend', function () {
      toast.remove();
    }, { once: true });
  }, 3000);
}

document.addEventListener('DOMContentLoaded', function () {
  // Mobile sidebar toggle
  var sidebarBtn = document.getElementById('dash-mobile-menu-btn');
  var sidebar = document.getElementById('dash-sidebar');
  var overlay = document.getElementById('dash-sidebar-overlay');

  function closeSidebar() {
    if (!sidebar) return;
    sidebar.classList.remove('open');
    if (overlay) overlay.classList.remove('active');
    if (sidebarBtn) sidebarBtn.setAttribute('aria-expanded', 'false');
  }

  if (sidebarBtn && sidebar) {
    sidebarBtn.addEventListener('click', function () {
      var isOpen = sidebar.classList.toggle('open');
      if (overlay) overlay.classList.toggle('active', isOpen);
      sidebarBtn.setAttribute('aria-expanded', String(isOpen));
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && sidebar.classList.contains('open')) {
        closeSidebar();
        sidebarBtn.focus();
      }
    });
  }

  if (overlay) {
    overlay.addEventListener('click', closeSidebar);
  }

  // Favorite toggle (AJAX)
  document.querySelectorAll('[data-favorite-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();

      var url = btn.getAttribute('data-url');
      if (!url) return;

      fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
            ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            : '',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
        .then(function (res) {
          if (!res.ok) throw new Error('Request failed');
          return res.json();
        })
        .then(function (data) {
          var wasFavorited = data.favorited;
          btn.classList.toggle('active', wasFavorited);
          btn.classList.remove('pop');
          void btn.offsetWidth;
          btn.classList.add('pop');
          btn.setAttribute('aria-label', wasFavorited ? 'Remove from favorites' : 'Add to favorites');
          showToast(wasFavorited ? 'Added to favorites' : 'Removed from favorites', wasFavorited ? 'success' : 'info');
        })
        .catch(function () {
          showToast('Could not update favorites. Please try again.', 'error');
        });
    });
  });

  // Sidebar link active state
  document.querySelectorAll('.dash-sidebar-link').forEach(function (link) {
    link.addEventListener('click', function () {
      document.querySelectorAll('.dash-sidebar-link').forEach(function (l) { l.classList.remove('active'); });
      this.classList.add('active');
    });
  });

  // Global search suggestions
  var searchForm = document.querySelector('.dash-search[data-global-search]');
  if (searchForm) {
    var input = searchForm.querySelector('[data-global-search-input]');
    var resultsBox = searchForm.querySelector('[data-global-search-results]');
    var timer = null;
    var activeIndex = -1;
    var endpoint = searchForm.getAttribute('action') || '/search';

    function resultsLinks() {
      return Array.prototype.slice.call(resultsBox.querySelectorAll('a'));
    }

    function setActive(index) {
      var links = resultsLinks();
      activeIndex = index;
      links.forEach(function (link, i) {
        link.classList.toggle('active', i === activeIndex);
      });
      if (links[activeIndex]) {
        links[activeIndex].focus({ preventScroll: true });
      }
    }

    function showLoading() {
      resultsBox.innerHTML = '<div class="search-suggest-loading"><i class="fas fa-circle-o-notch fa-spin"></i> Searching&hellip;</div>';
      resultsBox.hidden = false;
      input.setAttribute('aria-expanded', 'true');
    }

    function hideResults() {
      resultsBox.hidden = true;
      activeIndex = -1;
      input.setAttribute('aria-expanded', 'false');
    }

    input.addEventListener('input', function () {
      var q = input.value.trim();
      clearTimeout(timer);
      activeIndex = -1;

      if (q.length < 2) {
        hideResults();
        return;
      }

      showLoading();
      timer = setTimeout(function () {
        fetch(endpoint + '/instant?q=' + encodeURIComponent(q), {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
          .then(function (res) { return res.json(); })
          .then(function (data) {
            resultsBox.innerHTML = data.html;
            resultsBox.hidden = false;
            activeIndex = -1;
            input.setAttribute('aria-expanded', 'true');
          })
          .catch(function () {
            hideResults();
            showToast('Search failed. Please try again.', 'error');
          });
      }, 250);
    });

    document.addEventListener('click', function (e) {
      if (!searchForm.contains(e.target)) {
        hideResults();
      }
    });

    document.addEventListener('keydown', function (e) {
      if (resultsBox.hidden) return;

      if (e.key === 'Escape') {
        hideResults();
        return;
      }

      if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter') {
        var links = resultsLinks();
        if (!links.length) return;

        if (e.key === 'ArrowDown') {
          e.preventDefault();
          setActive(Math.min(activeIndex + 1, links.length - 1));
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          setActive(Math.max(activeIndex - 1, 0));
        } else if (e.key === 'Enter') {
          if (activeIndex >= 0 && links[activeIndex]) {
            e.preventDefault();
            links[activeIndex].click();
          }
        }
      }
    });
  }
});
