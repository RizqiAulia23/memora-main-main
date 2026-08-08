@foreach ($photos as $memory)
  <figure class="gal-item reveal" data-gallery-item data-title="{{ $memory->title }}" data-date="{{ $memory->memory_date->format('M j, Y') }}" data-src="{{ asset('storage/' . $memory->image) }}" data-url="{{ route('memories.show', $memory) }}">
    <img src="{{ asset('storage/' . $memory->image) }}" alt="{{ $memory->title }}" loading="lazy" />
    <figcaption>
      <span>{{ $memory->title }}</span>
      <a href="{{ route('gallery.download', $memory) }}" class="gal-download" aria-label="Download" title="Download"><i class="fas fa-download"></i></a>
    </figcaption>
  </figure>
@endforeach
