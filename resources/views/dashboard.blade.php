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
  <link rel="stylesheet" href="{{ assetv('css/base.css') }}">
  <link rel="stylesheet" href="{{ assetv('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ assetv('css/couple.css') }}">
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

        @include('partials.flash-alerts')

        <!-- Welcome Hero -->
        <section class="dash-welcome reveal" data-gsap-reveal aria-label="Welcome message">
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
        <section class="dash-stats reveal reveal-delay-1" data-gsap-reveal aria-label="Statistics">
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

        <!-- Relationship Overview -->
        @if ($couple)
          <section class="cpl-overview reveal reveal-delay-1" data-gsap-reveal aria-label="Relationship overview">
            <div class="dash-section-header">
              <h3><i class="fas fa-heart-circle-check"></i> {{ $couple['partner']->name }} &amp; You</h3>
              <a href="{{ route('couple-timeline.index') }}">Couple Timeline <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="cpl-overview-grid">
              <a href="{{ route('shared-memories.index') }}" class="cpl-ov-card">
                <div class="cpl-ov-icon"><i class="fas fa-share-nodes"></i></div>
                <div class="cpl-ov-body">
                  <div class="cpl-ov-value">{{ $couple['shared_memories'] }}</div>
                  <div class="cpl-ov-label">Shared Memories</div>
                </div>
              </a>
              <a href="{{ route('calendar.index') }}" class="cpl-ov-card">
                <div class="cpl-ov-icon"><i class="fas fa-calendar-star"></i></div>
                <div class="cpl-ov-body">
                  <div class="cpl-ov-value">{{ $couple['events'] }}</div>
                  <div class="cpl-ov-label">Shared Events</div>
                </div>
              </a>
              <a href="{{ route('playlists.index') }}" class="cpl-ov-card">
                <div class="cpl-ov-icon"><i class="fas fa-headphones"></i></div>
                <div class="cpl-ov-body">
                  <div class="cpl-ov-value">{{ $couple['playlist_tracks'] }}</div>
                  <div class="cpl-ov-label">Playlist Tracks</div>
                </div>
              </a>
              <a href="{{ route('bucket-list.index') }}" class="cpl-ov-card">
                <div class="cpl-ov-icon"><i class="fas fa-list-check"></i></div>
                <div class="cpl-ov-body">
                  <div class="cpl-ov-value">{{ $couple['bucket_done'] }}<span style="font-size:12px;font-weight:600;"> / {{ $couple['bucket_total'] }}</span></div>
                  <div class="cpl-ov-label">Bucket List Done</div>
                </div>
              </a>
            </div>
            @if ($couple['upcoming_date'])
              <div class="dash-anniversary" style="margin-top:14px;">
                <i class="fas fa-calendar-heart"></i>
                <span>
                  @php
                    $days = (int) now()->startOfDay()->diffInDays($couple['upcoming_date']['occurrence']->startOfDay());
                  @endphp
                  @if ($days === 0)
                    {{ $couple['upcoming_date']['title'] }} is today. Celebrate!
                  @else
                    {{ $couple['upcoming_date']['title'] }} in {{ $days }} {{ Str::plural('day', $days) }} — {{ $couple['upcoming_date']['occurrence']->format('M j, Y') }}.
                  @endif
                </span>
              </div>
            @endif
          </section>
        @endif

        <!-- Main Grid: Memories + Sidebar -->
        <div class="dash-grid">

          <!-- Recent Memories -->
          <section class="dash-section reveal reveal-delay-2" data-gsap-reveal aria-label="Recent memories">
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
            <section class="dash-section reveal reveal-delay-3" data-gsap-reveal aria-label="Timeline preview">
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
            <section class="dash-section reveal reveal-delay-3" data-gsap-reveal aria-label="Calendar">
              <div class="dash-section-body">
                <div class="dash-calendar">
                  <div class="dash-calendar-header">
                    <h4>{{ now()->format('F Y') }}</h4>
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
          <section class="dash-section reveal reveal-delay-4" data-gsap-reveal aria-label="Latest photos">
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
          <section class="dash-section reveal reveal-delay-4" data-gsap-reveal aria-label="Recent love letters">
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
          <section class="dash-section reveal reveal-delay-4" data-gsap-reveal aria-label="Quick actions">
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
          <section class="dash-section reveal reveal-delay-4" data-gsap-reveal aria-label="Recent activity">
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

  @vite('resources/js/memorify-animations.js')
  @vite('resources/js/dashboard-animations.js')
  <script src="{{ assetv('js/main.js') }}"></script>
  <script src="{{ assetv('js/dashboard.js') }}"></script>
</body>
</html>
