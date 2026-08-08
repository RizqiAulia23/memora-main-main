<h3 class="cal-details-title">{{ $date->format('F j, Y') }}</h3>
@forelse ($memories as $memory)
  <a href="{{ route('memories.show', $memory) }}" class="cal-details-item">
    @if ($memory->image)
      <img src="{{ asset('storage/' . $memory->image) }}" alt="{{ $memory->title }}" loading="lazy" />
    @else
      <div class="cal-details-ph">
        <i class="fas fa-images"></i>
      </div>
    @endif
    <div>
      <div class="cal-details-name">{{ $memory->title }}</div>
      <div class="cal-details-desc">{{ Str::limit($memory->description, 60) }}</div>
    </div>
  </a>
@empty
  <div class="cal-details-empty">
    <i class="fas fa-heart"></i>
    <p>No memories on this day.</p>
  </div>
@endforelse
