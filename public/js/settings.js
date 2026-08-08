document.addEventListener('DOMContentLoaded', function () {
  // Theme picker
  document.querySelectorAll('.set-theme-option input[name="theme"]').forEach(function (radio) {
    radio.addEventListener('change', function () {
      document.querySelectorAll('.set-theme-option').forEach(function (opt) {
        opt.classList.toggle('selected', opt.querySelector('input').checked);
      });
    });
  });
});
