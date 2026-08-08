<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Letter - Memorify</title>
  <meta name="description" content="Edit your love letter." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="{{ asset('css/base.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/letters.css') }}">
</head>
<body>

  <div class="dash-layout">

    @include('partials.dashboard-sidebar', ['activeSidebar' => 'letters'])

    <main class="dash-main">

      @include('partials.dashboard-topbar')

      <div class="dash-content">

        <!-- Page Header -->
        <section class="mem-head reveal">
          <div>
            <h1 class="mem-head-title">Edit Letter</h1>
            <p class="mem-head-sub">Revise your words of love.</p>
          </div>
          <a href="{{ route('letters.show', $loveLetter) }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </section>

        <section class="letter-form-wrap reveal reveal-delay-1">
          <form method="POST" action="{{ route('letters.update', $loveLetter) }}" class="letter-form">
            @csrf
            @method('PUT')

            <div class="form-group">
              <label for="title">Title</label>
              <input type="text" id="title" name="title" value="{{ old('title', $loveLetter->title) }}" class="form-control @error('title') is-invalid @enderror" />
              @error('title')
                <span class="form-error">{{ $message }}</span>
              @enderror
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="mood">Mood</label>
                <select id="mood" name="mood" class="form-control @error('mood') is-invalid @enderror">
                  @foreach (\App\Enums\LoveLetterMood::cases() as $mood)
                    <option value="{{ $mood->value }}" {{ old('mood', $loveLetter->mood->value) === $mood->value ? 'selected' : '' }}>
                      {{ $mood->label() }}
                    </option>
                  @endforeach
                </select>
                @error('mood')
                  <span class="form-error">{{ $message }}</span>
                @enderror
              </div>
              <div class="form-group">
                <label for="letter_date">Date</label>
                <input type="date" id="letter_date" name="letter_date" value="{{ old('letter_date', $loveLetter->letter_date->format('Y-m-d')) }}" class="form-control @error('letter_date') is-invalid @enderror" />
                @error('letter_date')
                  <span class="form-error">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <div class="form-group">
              <label for="content">Letter</label>
              <div class="rte" data-rte-toolbar>
                <button type="button" data-rte-cmd="bold" title="Bold"><i class="fas fa-bold"></i></button>
                <button type="button" data-rte-cmd="italic" title="Italic"><i class="fas fa-italic"></i></button>
                <button type="button" data-rte-cmd="underline" title="Underline"><i class="fas fa-underline"></i></button>
                <button type="button" data-rte-cmd="insertUnorderedList" title="List"><i class="fas fa-list-ul"></i></button>
                <button type="button" data-rte-cmd="formatBlock" data-rte-value="H2" title="Heading"><i class="fas fa-heading"></i></button>
                <button type="button" data-rte-cmd="formatBlock" data-rte-value="BLOCKQUOTE" title="Quote"><i class="fas fa-quote-right"></i></button>
              </div>
              <div class="rte-editor form-control @error('content') is-invalid @enderror" contenteditable="true" data-rte-editor></div>
              <textarea id="content" name="content" class="rte-hidden" hidden>{{ old('content', $loveLetter->content) }}</textarea>
              @error('content')
                <span class="form-error">{{ $message }}</span>
              @enderror
            </div>

            <div class="form-check">
              <input type="checkbox" id="is_pinned" name="is_pinned" value="1" {{ old('is_pinned', $loveLetter->is_pinned) ? 'checked' : '' }} />
              <label for="is_pinned">Pin this letter to the top</label>
            </div>

            <div class="letter-form-actions">
              <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
              <a href="{{ route('letters.show', $loveLetter) }}" class="btn btn-outline">Cancel</a>
            </div>
          </form>
        </section>

      </div>
    </main>

  </div>

  <div class="toast-container" id="toast-container" aria-live="polite"></div>

  <script src="{{ asset('js/main.js') }}"></script>
  <script src="{{ asset('js/dashboard.js') }}"></script>
  <script src="{{ asset('js/letters.js') }}"></script>
</body>
</html>
