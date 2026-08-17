@foreach ($photos as $memory)
  <figure class="gal-item reveal" data-gsap-reveal data-gallery-item data-title="{{ $memory->title }}" data-date="{{ $memory->memory_date->format('M j, Y') }}" data-src="{{ $memory->imageUrl() }}" data-url="{{ route('memories.show', $memory) }}" tabindex="0" role="button" aria-label="Open photo: {{ $memory->title }}">
    <img src="{{ $memory->imageUrl() }}" alt="{{ $memory->title }}" loading="lazy" />
    <figcaption>
      <span>{{ $memory->title }}</span>
      @if ($memory->user_id !== auth()->id())
        <span class="gal-shared-badge"><i class="fas fa-share-nodes"></i> Shared by {{ $memory->user?->name ?? 'partner' }}</span>
      @endif
      <a href="{{ route('gallery.download', $memory) }}" class="gal-download" aria-label="Download {{ $memory->title }}" title="Download"><i class="fas fa-download"></i></a>
    </figcaption>
  </figure>
@endforeach
