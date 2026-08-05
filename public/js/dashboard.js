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

  // Favorite toggle
  document.querySelectorAll('.dash-memory-fav').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      var isActive = this.classList.contains('active');
      if (isActive) {
        this.classList.remove('active');
        this.innerHTML = '<i class="far fa-heart"></i>';
      } else {
        this.classList.add('active');
        this.innerHTML = '<i class="fas fa-heart"></i>';
      }
    });
  });

  // Sidebar link active state
  document.querySelectorAll('.dash-sidebar-link').forEach(function (link) {
    link.addEventListener('click', function () {
      document.querySelectorAll('.dash-sidebar-link').forEach(function (l) { l.classList.remove('active'); });
      this.classList.add('active');
    });
  });
});
