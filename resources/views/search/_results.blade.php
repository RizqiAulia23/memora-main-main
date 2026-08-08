<div class="search-results" data-search-results>
  <div class="search-empty-hint">
    {{ $results['memories']->isEmpty() && $results['photos']->isEmpty() && $results['letters']->isEmpty()
        ? 'No results found for "' . e($query) . '".'
        : count($results['memories']) + count($results['photos']) + count($results['letters']) . ' result(s) for "' . e($query) . '".' }}
  </div>

  @if ($results['memories']->isNotEmpty())
    <h2 class="search-group-title"><i class="fas fa-images"></i> Memories</h2>
    <div class="search-card-list">
      @foreach ($results['memories'] as $memory)
        <a href="{{ route('memories.show', $memory) }}" class="search-card">
          @if ($memory->image)
            <img src="{{ asset('storage/' . $memory->image) }}" alt="{{ $memory->title }}" loading="lazy" />
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
          <img src="{{ asset('storage/' . $memory->image) }}" alt="{{ $memory->title }}" loading="lazy" />
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
</div>
