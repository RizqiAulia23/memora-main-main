document.addEventListener('DOMContentLoaded', function () {
  var fileInput = document.getElementById('image');
  var preview = document.getElementById('image-preview');

  if (!fileInput || !preview) return;

  fileInput.addEventListener('change', function () {
    var file = this.files[0];
    if (!file) return;

    var reader = new FileReader();
    reader.onload = function (e) {
      var img = preview.querySelector('img');
      if (img) {
        img.src = e.target.result;
        preview.hidden = false;
      }
    };
    reader.readAsDataURL(file);
  });
});
