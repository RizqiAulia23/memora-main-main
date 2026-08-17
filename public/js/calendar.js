document.addEventListener('DOMContentLoaded', function () {
  var details = document.querySelector('.cal-details');
  var endpoint = details ? details.getAttribute('data-endpoint') : null;

  if (!details || !endpoint) return;

  var requestId = 0;

  document.querySelectorAll('[data-cal-day]').forEach(function (day) {
    day.addEventListener('click', function () {
      document.querySelectorAll('[data-cal-day]').forEach(function (d) {
        d.classList.remove('selected');
        d.removeAttribute('aria-pressed');
      });
      day.classList.add('selected');
      day.setAttribute('aria-pressed', 'true');

      details.innerHTML =
        '<div class="cal-details-empty cal-details-loading"><i class="fas fa-circle-o-notch fa-spin"></i><p>Loading memories&hellip;</p></div>';
      details.setAttribute('aria-busy', 'true');

      var date = day.getAttribute('data-date');
      var myRequest = ++requestId;
      fetch(endpoint + '?date=' + encodeURIComponent(date), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(function (res) {
          if (!res.ok) throw new Error('Request failed');
          return res.json();
        })
        .then(function (data) {
          if (myRequest !== requestId) return;
          details.innerHTML = data.html;
          details.removeAttribute('aria-busy');
        })
        .catch(function () {
          if (myRequest !== requestId) return;
          details.innerHTML =
            '<div class="cal-details-empty"><i class="fas fa-heart"></i><p>Could not load memories. Please try again.</p></div>';
          details.removeAttribute('aria-busy');
        });
    });
  });
});
