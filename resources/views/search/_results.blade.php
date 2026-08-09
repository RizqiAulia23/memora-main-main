<div class="search-results" data-search-results>
  @if ($results['memories']->isEmpty() && $results['photos']->isEmpty() && $results['letters']->isEmpty())
    <div class="dash-section mem-empty">
      <div class="dash-empty">
        <div class="dash-empty-icon"><i class="fas fa-search-minus"></i></div>
        <p>No results found for "{{ e($query) }}". Try a different word, date, or feeling.</p>
        <div class="dash-empty-actions">
          <a href="{{ route('memories.index') }}" class="btn btn-outline btn-sm">Browse All Memories</a>
          <a href="{{ route('memories.create') }}" class="btn btn-primary btn-sm">Add a Memory</a>
        </div>
      </div>
    </div>
  @else
    <div class="search-empty-hint">
      {{ count($results['memories']) + count($results['photos']) + count($results['letters']) }} result(s) for "{{ e($query) }}".
    </div>

    @if ($results['memories']->isNotEmpty())
      <h2 class="search-group-title"><i class="fas fa-images"></i> Memories</h2>
      <div class="search-card-list">
        @foreach ($results['memories'] as $memory)
          <a href="{{ route('memories.show', $memory) }}" class="search-card">
            @if ($memory->image)
              <img src="{{ $memory->imageUrl() }}" alt="{{ $memory->title }}" loading="lazy" />
            @endif
            <div class="search-card-body">
              <div class="search-card-title">{{ $memory->title }}</div>
              <div class="search-card-meta">{{ $memory->memory_date->format('M j, Y') }}</div>
            </div>
          </a>
        @endforeach
      </div>
    @endif

    @if ($results['photos']->isNotEmpty())
      <h2 class="search-group-title"><i class="fas fa-camera"></i> Photos</h2>
      <div class="search-photo-grid">
        @foreach ($results['photos'] as $memory)
          <a href="{{ route('memories.show', $memory) }}" class="search-photo" title="{{ $memory->title }}">
            <img src="{{ $memory->imageUrl() }}" alt="{{ $memory->title }}" loading="lazy" />
          </a>
        @endforeach
      </div>
    @endif

    @if ($results['letters']->isNotEmpty())
      <h2 class="search-group-title"><i class="fas fa-envelope-open-text"></i> Love Letters</h2>
      <div class="search-card-list">
        @foreach ($results['letters'] as $letter)
          <a href="{{ route('letters.show', $letter) }}" class="search-card">
            <div class="search-card-body">
              <div class="search-card-title"><i class="{{ $letter->mood->icon() }} search-letter-mood"></i> {{ $letter->title }}</div>
              <div class="search-card-meta">{{ $letter->letter_date->format('M j, Y') }}</div>
            </div>
          </a>
        @endforeach
      </div>
    @endif
  @endif
</div>
