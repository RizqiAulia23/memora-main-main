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

  if (sidebarBtn && sidebar && overlay) {
    sidebarBtn.addEventListener('click', function () {
      sidebar.classList.toggle('open');
      overlay.classList.toggle('active');
    });
    overlay.addEventListener('click', function () {
      sidebar.classList.remove('open');
      overlay.classList.remove('active');
    });
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
    var endpoint = searchForm.getAttribute('action') || '/search';

    input.addEventListener('input', function () {
      var q = input.value.trim();
      clearTimeout(timer);

      if (q.length < 2) {
        resultsBox.hidden = true;
        return;
      }

      timer = setTimeout(function () {
        fetch(endpoint + '/instant?q=' + encodeURIComponent(q), {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
          .then(function (res) { return res.json(); })
          .then(function (data) {
            resultsBox.innerHTML = data.html;
            resultsBox.hidden = false;
          })
          .catch(function () {
            resultsBox.hidden = true;
          });
      }, 250);
    });

    document.addEventListener('click', function (e) {
      if (!searchForm.contains(e.target)) {
        resultsBox.hidden = true;
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        resultsBox.hidden = true;
      }
    });
  }
});
