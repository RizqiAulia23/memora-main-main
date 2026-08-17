<h3 class="cal-details-title">{{ $date->format('F j, Y') }}</h3>
@if ($events->isNotEmpty())
  <div class="cal-details-events">
    @foreach ($events as $event)
      <a href="{{ route('events.show', $event) }}" class="cal-details-item">
        <div class="cal-details-ph cal-details-ph-event">
          <i class="fas fa-calendar-day"></i>
        </div>
        <div>
          <div class="cal-details-name">{{ $event->title }}</div>
          <div class="cal-details-desc">
            @if ($event->event_time)
              {{ $event->event_time->format('H:i') }} &middot;
            @endif
            {{ $event->user->name }}
          </div>
        </div>
      </a>
    @endforeach
  </div>
@endif
@forelse ($memories as $memory)
  <a href="{{ route('memories.show', $memory) }}" class="cal-details-item">
    @if ($memory->image)
      <img src="{{ $memory->imageUrl() }}" alt="{{ $memory->title }}" loading="lazy" />
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
  @if ($events->isEmpty())
    <div class="cal-details-empty">
      <i class="fas fa-heart"></i>
      <p>No memories on this day.</p>
    </div>
  @endif
@endforelse