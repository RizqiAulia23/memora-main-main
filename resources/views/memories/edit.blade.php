<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Memory - Memorify</title>
  <meta name="description" content="Edit a memory." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="{{ asset('css/base.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/memories.css') }}">
</head>
<body>

  <div class="dash-layout">

    @include('partials.dashboard-sidebar', ['activeSidebar' => 'memories'])

    <main class="dash-main">

      @include('partials.dashboard-topbar')

      <div class="dash-content">

        <!-- Page Header -->
        <section class="mem-head reveal">
          <div>
            <h1 class="mem-head-title">Edit Memory</h1>
            <p class="mem-head-sub">Update the details of this special moment.</p>
          </div>
          <a href="{{ route('memories.show', $memory) }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Memory
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
        <section class="dash-section reveal reveal-delay-1">
          <div class="dash-section-body">
            <form action="{{ route('memories.update', $memory) }}" method="POST" enctype="multipart/form-data" class="mem-form" novalidate>
              @csrf
              @method('PATCH')

              <div class="mem-form-grid">
                <div class="mem-form-field">
                  <label for="title" class="mem-form-label">Title</label>
                  <input
                    type="text"
                    id="title"
                    name="title"
                    class="mem-form-input {{ $errors->has('title') ? 'mem-form-input-error' : '' }}"
                    value="{{ old('title', $memory->title) }}"
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
                    value="{{ old('memory_date', $memory->memory_date->format('Y-m-d')) }}"
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
                >{{ old('description', $memory->description) }}</textarea>
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
                <div class="mem-preview" id="image-preview" @if (!$memory->image) hidden @endif>
                  <img src="{{ $memory->imageUrl() }}" alt="Current photo" />
                </div>
              </div>

              <div class="mem-form-actions">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-check"></i> Update Memory
                </button>
                <a href="{{ route('memories.show', $memory) }}" class="btn btn-outline">Cancel</a>
              </div>
            </form>
          </div>
        </section>

      </div>
    </main>

  </div>

  <script src="{{ asset('js/main.js') }}"></script>
  <script src="{{ asset('js/dashboard.js') }}"></script>
  <script src="{{ asset('js/memories.js') }}"></script>
</body>
</html>
