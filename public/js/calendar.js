document.addEventListener('DOMContentLoaded', function () {
  var details = document.querySelector('.cal-details');
  var endpoint = details ? details.getAttribute('data-endpoint') : null;

  if (!details || !endpoint) return;

  document.querySelectorAll('[data-cal-day]').forEach(function (day) {
    day.addEventListener('click', function () {
      var date = day.getAttribute('data-date');
      fetch(endpoint + '?date=' + encodeURIComponent(date), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          details.innerHTML = data.html;
        })
        .catch(function () {
          details.innerHTML =
            '<div class="cal-details-empty"><i class="fas fa-heart"></i><p>Could not load memories. Please try again.</p></div>';
        });
    });
  });
});
