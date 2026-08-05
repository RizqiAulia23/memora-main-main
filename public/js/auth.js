// Password visibility toggle
function togglePassword() {
  var input = document.getElementById('password');
  var icon = document.getElementById('eye-icon');
  if (!input || !icon) return;

  if (input.type === 'password') {
    input.type = 'text';
    icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
  } else {
    input.type = 'password';
    icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
  }
}

// Form submit simulation
document.addEventListener('DOMContentLoaded', function() {
  var loginForm = document.getElementById('login-form');
  var registerForm = document.getElementById('register-form');

  if (loginForm) {
    loginForm.addEventListener('submit', function () {

        const btn = document.getElementById('btn-submit');

        btn.innerHTML = 'Signing in...';
        btn.disabled = true;

    });
}

  if (registerForm) {
    registerForm.addEventListener('submit', function () {

        const btn = document.getElementById('btn-submit');

        btn.innerHTML = 'Creating account...';
        btn.disabled = true;

    });
}