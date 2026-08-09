<!doctype html>
<html lang="en" data-theme="{{ $theme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - Memorify</title>
  <meta name="description" content="Your personal memory dashboard. Cherish every moment." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="{{ asset('css/base.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>

  <div class="dash-layout">

    <!-- ================================
         SIDEBAR
         ================================ -->
    @include('partials.dashboard-sidebar', ['activeSidebar' => 'dashboard'])

    <!-- ================================
         MAIN CONTENT
         ================================ -->
    <main class="dash-main">

      <!-- Top Navbar -->
      @include('partials.dashboard-topbar')

      <!-- Dashboard Content -->
      <div class="dash-content">

        @if (session('success'))
          <div class="dash-alert dash-alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
          </div>
        @endif

        @if (session('error'))
          <div class="dash-alert dash-alert-error">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
          </div>
        @endif

        <!-- Welcome Hero -->
        <section class="dash-welcome reveal" aria-label="Welcome message">
          <div class="dash-welcome-content">
            @php
              $hour = (int) now()->format('G');
              $greeting = $hour < 12 ? 'Good Morning' : ($hour < 18 ? 'Good Afternoon' : 'Good Evening');
            @endphp
            <h1>{{ $greeting }}, {{ auth()->user()->name }} <span class="wave">&#x1F44B;</span></h1>
            <p>Every beautiful memory deserves a safe place to live forever. Let's cherish today together.</p>
            @if ($anniversary)
              <div class="dash-anniversary">
                <i class="fas fa-ring"></i>
                <span>
                  @if ($anniversary['days'] === 0)
                    Today is your anniversary. Happy anniversary!
                  @else
                    {{ $anniversary['days'] }} {{ Str::plural('day', $anniversary['days']) }} until your anniversary on {{ $anniversary['label'] }}.
                  @endif
                </span>
              </div>
            @endif
          </div>
          <div class="dash-welcome-visual">
            <div class="dash-welcome-hearts">
              <i class="fas fa-heart"></i>
              <i class="fas fa-heart"></i>
              <i class="fas fa-heart"></i>
            </div>
          </div>
        </section>

        <!-- Statistics -->
        <section class="dash-stats reveal reveal-delay-1" aria-label="Statistics">
          <div class="dash-stat-card">
            <div class="dash-stat-icon pink"><i class="fas fa-images"></i></div>
            <div class="dash-stat-number" data-count="{{ $totalMemories }}" data-suffix="">0</div>
            <div class="dash-stat-label">Total Memories</div>
            <div class="dash-stat-trend up"><i class="fas fa-arrow-up"></i> {{ $newThisMonth }} this month</div>
          </div>
          <div class="dash-stat-card">
            <div class="dash-stat-icon purple"><i class="fas fa-heart"></i></div>
            <div class="dash-stat-number" data-count="{{ $totalFavorites }}" data-suffix="">0</div>
            <div class="dash-stat-label">Favorite Memories</div>
            <div class="dash-stat-trend up"><i class="fas fa-heart"></i> Favorites</div>
          </div>
          <div class="dash-stat-card">
            <div class="dash-stat-icon peach"><i class="fas fa-envelope-open-text"></i></div>
            <div class="dash-stat-number" data-count="{{ $totalLoveLetters }}" data-suffix="">0</div>
            <div class="dash-stat-label">Love Letters</div>
            <div class="dash-stat-trend up"><i class="fas fa-envelope-open-text"></i> Letters</div>
          </div>
          <div class="dash-stat-card">
            <div class="dash-stat-icon teal"><i class="fas fa-camera"></i></div>
            <div class="dash-stat-number" data-count="{{ $totalPhotos }}" data-suffix="">0</div>
            <div class="dash-stat-label">Total Photos</div>
            <div class="dash-stat-trend up"><i class="fas fa-camera"></i> Photo memories</div>
          </div>
        </section>

        <!-- Main Grid: Memories + Sidebar -->
        <div class="dash-grid">

          <!-- Recent Memories -->
          <section class="dash-section reveal reveal-delay-2" aria-label="Recent memories">
            <div class="dash-section-header">
              <h3>Recent Memories</h3>
              <a href="{{ route('memories.index') }}">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="dash-section-body">
              @if ($recentMemories->isNotEmpty())
                <div class="dash-memories-grid">
                  @foreach ($recentMemories as $memory)
                    <article class="dash-memory-card" onclick="window.location='{{ route('memories.show', $memory) }}'">
                      <div class="dash-memory-img">
                        <img src="{{ $memory->imageUrl() }}" alt="{{ $memory->title }}" loading="lazy" />
                      </div>
                      <div class="dash-memory-info">
                        <a href="{{ route('memories.show', $memory) }}" class="dash-memory-title-link">
                          <div class="dash-memory-title">{{ $memory->title }}</div>
                        </a>
                        <div class="dash-memory-meta">
                          <span><i class="fas fa-calendar"></i> {{ $memory->memory_date->format('M j, Y') }}</span>
                        </div>
                      </div>
                    </article>
                  @endforeach
                </div>
              @else
                <div class="dash-empty">
                  <div class="dash-empty-icon"><i class="fas fa-images"></i></div>
                  <p>No memories yet. Start preserving your beautiful moments today.</p>
                  <a href="{{ route('memories.create') }}" class="btn btn-primary btn-sm">Add Your First Memory</a>
                </div>
              @endif
            </div>
          </section>

          <!-- Right Sidebar Column -->
          <div class="dash-sidebar-col">

            <!-- Timeline Preview -->
            <section class="dash-section reveal reveal-delay-3" aria-label="Timeline preview">
              <div class="dash-section-header">
                <h3>Our Journey</h3>
                <a href="{{ route('memories.index', ['sort' => 'memory_date']) }}">Full Timeline <i class="fas fa-arrow-right"></i></a>
              </div>
              <div class="dash-section-body">
                @if ($timeline->isNotEmpty())
                  <div class="dash-timeline">
                    @foreach ($timeline as $memory)
                      <div class="dash-timeline-item">
                        <div class="dash-timeline-dot"></div>
                        <div class="dash-timeline-content">
                          <div class="dash-timeline-icon">&#x1F495;</div>
                          <h4>{{ $memory->title }}</h4>
                          <span>{{ $memory->memory_date->format('M j, Y') }}</span>
                        </div>
                      </div>
                    @endforeach
                  </div>
                @else
                  <div class="dash-empty">
                    <p>Your journey timeline will appear here as you add memories.</p>
                  </div>
                @endif
              </div>
            </section>

            <!-- Calendar Widget -->
            <section class="dash-section reveal reveal-delay-3" aria-label="Calendar">
              <div class="dash-section-body">
                <div class="dash-calendar">
                  <div class="dash-calendar-header">
                    <h4>{{ now()->format('F Y') }}</h4>
                    <div class="dash-calendar-nav">
                      <button aria-label="Previous month"><i class="fas fa-chevron-left"></i></button>
                      <button aria-label="Next month"><i class="fas fa-chevron-right"></i></button>
                    </div>
                  </div>
                  <div class="dash-calendar-grid">
                    <div class="dash-calendar-day-name">Su</div>
                    <div class="dash-calendar-day-name">Mo</div>
                    <div class="dash-calendar-day-name">Tu</div>
                    <div class="dash-calendar-day-name">We</div>
                    <div class="dash-calendar-day-name">Th</div>
                    <div class="dash-calendar-day-name">Fr</div>
                    <div class="dash-calendar-day-name">Sa</div>

                    @foreach ($calendarDays as $cell)
                      <div class="dash-calendar-day
                        {{ $cell['isToday'] ? 'today' : '' }}
                        {{ $cell['hasMemory'] ? 'has-memory' : '' }}
                        {{ $cell['otherMonth'] ? 'other-month' : '' }}">
                        {{ $cell['day'] }}
                      </div>
                    @endforeach
                  </div>
                </div>
              </div>
            </section>

          </div>
        </div>

        <!-- Latest Gallery + Recent Letters -->
        <div class="dash-grid-split">

          <!-- Latest Photos -->
          <section class="dash-section reveal reveal-delay-4" aria-label="Latest photos">
            <div class="dash-section-header">
              <h3>Latest Photos</h3>
              <a href="{{ route('gallery.index') }}">Open Gallery <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="dash-section-body">
              @if ($latestGallery->isNotEmpty())
                <div class="dash-gallery-strip">
                  @foreach ($latestGallery as $memory)
                    <a href="{{ route('memories.show', $memory) }}" class="dash-gallery-thumb" title="{{ $memory->title }}">
                      <img src="{{ $memory->imageUrl() }}" alt="{{ $memory->title }}" loading="lazy" />
                    </a>
                  @endforeach
                </div>
              @else
                <div class="dash-empty">
                  <div class="dash-empty-icon"><i class="fas fa-camera"></i></div>
                  <p>No photos yet. Add memories with photos to fill your gallery.</p>
                  <a href="{{ route('memories.create') }}" class="btn btn-primary btn-sm">Add a Photo</a>
                </div>
              @endif
            </div>
          </section>

          <!-- Recent Letters -->
          <section class="dash-section reveal reveal-delay-4" aria-label="Recent love letters">
            <div class="dash-section-header">
              <h3>Recent Love Letters</h3>
              <a href="{{ route('letters.index') }}">All Letters <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="dash-section-body">
              @if ($recentLetters->isNotEmpty())
                <div class="dash-letter-strip">
                  @foreach ($recentLetters as $letter)
                    <a href="{{ route('letters.show', $letter) }}" class="dash-letter-card">
                      <div class="dash-letter-mood {{ $letter->mood->value }}"><i class="{{ $letter->mood->icon() }}"></i></div>
                      <div>
                        <div class="dash-letter-title">{{ $letter->title }}</div>
                        <div class="dash-letter-meta">{{ $letter->letter_date->format('M j, Y') }}</div>
                      </div>
                      @if ($letter->is_pinned)
                        <i class="fas fa-thumbtack dash-letter-pin"></i>
                      @endif
                    </a>
                  @endforeach
                </div>
              @else
                <div class="dash-empty">
                  <div class="dash-empty-icon"><i class="fas fa-envelope-open-text"></i></div>
                  <p>No love letters yet. Write your sweetheart a note.</p>
                  <a href="{{ route('letters.create') }}" class="btn btn-primary btn-sm">Write a Letter</a>
                </div>
              @endif
            </div>
          </section>

        </div>

        <!-- Bottom Row: Quick Actions + Activity -->
        <div class="dash-grid-split">

          <!-- Quick Actions -->
          <section class="dash-section reveal reveal-delay-4" aria-label="Quick actions">
            <div class="dash-section-header">
              <h3>Quick Actions</h3>
            </div>
            <div class="dash-section-body">
              <div class="dash-actions-grid">
                <a href="{{ route('memories.create') }}" class="dash-action-btn">
                  <div class="dash-action-icon pink"><i class="fas fa-plus"></i></div>
                  <span class="dash-action-text">Add Memory</span>
                </a>
                <a href="{{ route('gallery.index') }}" class="dash-action-btn">
                  <div class="dash-action-icon purple"><i class="fas fa-cloud-upload-alt"></i></div>
                  <span class="dash-action-text">Gallery</span>
                </a>
                <a href="{{ route('letters.create') }}" class="dash-action-btn">
                  <div class="dash-action-icon peach"><i class="fas fa-feather-alt"></i></div>
                  <span class="dash-action-text">Write Letter</span>
                </a>
                <a href="{{ route('timeline.index') }}" class="dash-action-btn">
                  <div class="dash-action-icon teal"><i class="fas fa-stream"></i></div>
                  <span class="dash-action-text">Timeline</span>
                </a>
              </div>
            </div>
          </section>

          <!-- Recent Activity -->
          <section class="dash-section reveal reveal-delay-4" aria-label="Recent activity">
            <div class="dash-section-header">
              <h3>Recent Activity</h3>
              <a href="{{ route('memories.index') }}">See All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="dash-section-body">
              <div class="dash-activity-list">
                @forelse ($activity as $item)
                  <div class="dash-activity-item">
                    <div class="dash-activity-icon pink"><i class="fas {{ $item->created_at->equalTo($item->updated_at) ? 'fa-camera' : 'fa-pen' }}"></i></div>
                    <div class="dash-activity-content">
                      <div class="dash-activity-text"><strong>{{ $item->created_at->equalTo($item->updated_at) ? 'Memory created' : 'Memory updated' }}</strong> "{{ $item->title }}"</div>
                      <div class="dash-activity-time">{{ $item->updated_at->diffForHumans() }}</div>
                    </div>
                  </div>
                @empty
                  <div class="dash-empty">
                    <p>No activity yet.</p>
                  </div>
                @endforelse
              </div>
            </div>
          </section>

        </div>

      </div>
    </main>

  </div>

  <div class="toast-container" id="toast-container" aria-live="polite"></div>

  <script src="{{ asset('js/main.js') }}"></script>
  <script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>
