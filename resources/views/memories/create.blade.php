<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Memory - Memorify</title>
  <meta name="description" content="Create a new memory." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="{{ assetv('css/base.css') }}">
  <link rel="stylesheet" href="{{ assetv('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ assetv('css/memories.css') }}">
</head>
<body>

  <div class="dash-layout">

    @include('partials.dashboard-sidebar', ['activeSidebar' => 'memories'])

    <main class="dash-main">

      @include('partials.dashboard-topbar')

      <div class="dash-content">

        <!-- Page Header -->
        <section class="mem-head reveal" data-gsap-reveal>
          <div>
            <h1 class="mem-head-title">Add Memory</h1>
            <p class="mem-head-sub">Preserve a beautiful moment forever.</p>
          </div>
          <a href="{{ route('memories.index') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Memories
          </a>
        </section>

        @include('partials.flash-alerts')

        @if ($errors->any())
          <div class="dash-alert mem-alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <div>
              <strong>Please fix the following:</strong>
              <ul class="mem-error-list">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          </div>
        @endif

        <!-- Form -->
        <section class="dash-section reveal reveal-delay-1" data-gsap-reveal>
          <div class="dash-section-body">
            <form action="{{ route('memories.store') }}" method="POST" enctype="multipart/form-data" class="mem-form" novalidate data-submit-feedback>
              @csrf

              <div class="mem-form-grid">
                <div class="mem-form-field">
                  <label for="title" class="mem-form-label">Title</label>
                  <input
                    type="text"
                    id="title"
                    name="title"
                    class="mem-form-input {{ $errors->has('title') ? 'mem-form-input-error' : '' }}"
                    value="{{ old('title') }}"
                    placeholder="e.g. Beach Sunset in Bali"
                    required
                  />
                </div>

                <div class="mem-form-field">
                  <label for="memory_date" class="mem-form-label">Memory Date</label>
                  <input
                    type="date"
                    id="memory_date"
                    name="memory_date"
                    class="mem-form-input {{ $errors->has('memory_date') ? 'mem-form-input-error' : '' }}"
                    value="{{ old('memory_date', now()->format('Y-m-d')) }}"
                    required
                  />
                </div>
              </div>

              <div class="mem-form-field">
                <label for="description" class="mem-form-label">Description</label>
                <textarea
                  id="description"
                  name="description"
                  rows="6"
                  class="mem-form-input mem-form-textarea {{ $errors->has('description') ? 'mem-form-input-error' : '' }}"
                  placeholder="Write about this special moment..."
                  required
                >{{ old('description') }}</textarea>
              </div>

              <div class="mem-form-field">
                <label for="image" class="mem-form-label">Photo <span class="mem-form-hint">(jpg, jpeg, png, webp - max 2MB)</span></label>
                <input
                  type="file"
                  id="image"
                  name="image"
                  accept=".jpg,.jpeg,.png,.webp"
                  class="mem-form-input mem-form-file {{ $errors->has('image') ? 'mem-form-input-error' : '' }}"
                />
                <div class="mem-preview" id="image-preview" hidden>
                  <img src="" alt="Image preview" />
                </div>
              </div>

              <div class="mem-form-actions">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-heart"></i> Save Memory
                </button>
                <a href="{{ route('memories.index') }}" class="btn btn-outline">Cancel</a>
              </div>
            </form>
          </div>
        </section>

      </div>
    </main>

  </div>

  @vite('resources/js/memorify-animations.js')
  @vite('resources/js/memories-form-animations.js')
  <script src="{{ assetv('js/main.js') }}"></script>
  <script src="{{ assetv('js/dashboard.js') }}"></script>
  <script src="{{ assetv('js/memories.js') }}"></script>
</body>
</html>
