@if (session('success'))
  <div class="dash-alert dash-alert-success" role="status">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
  </div>
@endif

@if (session('error'))
  <div class="dash-alert dash-alert-error" role="alert">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
  </div>
@endif