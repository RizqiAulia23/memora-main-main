// Tab switching functionality
document.addEventListener('DOMContentLoaded', function() {
  var tabBtns = document.querySelectorAll('.tab-btn');

  tabBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
      var tab = this.dataset.tab;

      // Remove active from all tabs and panels
      document.querySelectorAll('.tab-btn').forEach(function(b) {
        b.classList.remove('active');
      });
      document.querySelectorAll('.tab-panel').forEach(function(p) {
        p.classList.remove('active');
      });

      // Add active to clicked tab and corresponding panel
      this.classList.add('active');
      var panel = document.getElementById(tab + '-panel');
      if (panel) {
        panel.classList.add('active');
      }
    });
  });
});
