document.addEventListener('DOMContentLoaded', function () {
  var toolbar = document.querySelector('[data-rte-toolbar]');
  var editor = document.querySelector('[data-rte-editor]');
  var hidden = document.getElementById('content');

  if (!editor || !hidden) return;

  // Populate editor with existing content
  if (hidden.value) {
    editor.innerHTML = hidden.value;
  }

  function sync() {
    hidden.value = editor.innerHTML;
  }

  // Buttons
  if (toolbar) {
    toolbar.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-rte-cmd]');
      if (!btn) return;

      e.preventDefault();
      editor.focus();

      var command = btn.getAttribute('data-rte-cmd');
      var value = btn.getAttribute('data-rte-value') || null;

      document.execCommand(command, false, value);
      sync();
    });
  }

  // Sync on input & blur
  editor.addEventListener('input', sync);
  editor.addEventListener('blur', sync);

  // Ensure form submission includes content
  var form = editor.closest('form');
  if (form) {
    form.addEventListener('submit', sync);
  }
});
