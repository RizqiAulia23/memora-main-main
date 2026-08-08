document.addEventListener('DOMContentLoaded', function () {
  var form = document.querySelector('[data-avatar-form]');
  var input = form ? form.querySelector('[data-avatar-input]') : null;
  var preview = document.querySelector('[data-avatar-preview]');
  var saveBtn = document.querySelector('[data-avatar-save]');

  if (!input) return;

  input.addEventListener('change', function () {
    var file = input.files[0];
    if (!file) return;

    if (!/image\/(jpeg|png|webp)/.test(file.type)) {
      showToast('Please choose a JPG, PNG, or WebP image.', 'error');
      input.value = '';
      return;
    }

    if (file.size > 2048 * 1024) {
      showToast('Image must be 2MB or smaller.', 'error');
      input.value = '';
      return;
    }

    var reader = new FileReader();
    reader.onload = function (e) {
      if (preview && preview.tagName === 'IMG') {
        preview.src = e.target.result;
      } else if (preview) {
        var img = document.createElement('img');
        img.src = e.target.result;
        img.alt = 'Your avatar';
        img.setAttribute('data-avatar-preview', '');
        preview.replaceWith(img);
      }
      if (saveBtn) saveBtn.hidden = false;
    };
    reader.readAsDataURL(file);
  });
});
