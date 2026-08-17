# Copilot Instructions for Memorify

## Quick Start

**Setup**: `composer setup` (one command that installs deps, copies `.env`, generates key, migrates, builds assets)

**Development**: 
```bash
composer dev           # Runs server, queue listener, logs, and Vite dev all together
npm run dev           # Vite dev server with hot reload (standalone)
npm run build         # Build production frontend assets
php artisan serve     # Run just the dev server
```

**Testing & Quality**:
```bash
php artisan test                          # Run full test suite
php artisan test tests/Feature/MemorifyTest.php   # Run single test file
php artisan test --filter=test_user_can_login    # Run single test by name
vendor\bin\pint                           # Check & auto-fix code style
php artisan optimize:clear                # Clear config, routes, views cache
```

**Database**:
```bash
php artisan migrate              # Run pending migrations
php artisan migrate:fresh --seed # Rebuild DB and seed demo data
php artisan db:seed --force      # Re-run seeders (idempotent)
```

## Architecture

**Route Organization** (`routes/web.php`):
- Public pages: home, about, features, contact
- Auth routes: login, register, logout (with rate limiting)
- Authenticated routes grouped under `middleware('auth')`
- Subdomain/controller organization by feature (memories, gallery, letters, timeline, calendar, search, profile, settings)

**Controller Pattern** (thin, delegation-heavy):
- Dependency injection via constructor
- Authorization checks at the top via `$this->authorize()`
- Input validation delegated to Form Requests (e.g., `StoreMemoryRequest`)
- Heavy lifting delegated to Services (e.g., `MemoryImageService`, `DashboardService`)
- Resource cleanup in try/catch (e.g., if memory creation fails after upload, image is deleted)

**Model Structure** (Eloquent with query scopes):
- Minimal logic — relationships and scopes only
- Protected `$fillable` arrays
- Property casting (e.g., `memory_date => 'date'`)
- Query scopes for common filtering: `search()`, `sort()`, `favorited()`

**Authorization** (ownership-based):
- Every model that needs access control has a Policy (`MemoryPolicy`, `LoveLetterPolicy`)
- Policies check `$user->id === $model->user_id` for create/read/update/delete
- Controllers call `$this->authorize()` before accessing the resource
- Route-model binding automatically returns 404 for missing resources; policies return 403 for unauthorized access

**Service Layer** (reusable business logic):
- `ImageStore` — store/delete images in private storage
- `MemoryImageService` — memory-specific image handling
- `ProfileService` — profile + avatar operations
- `AccountService` — account deletion with file cleanup
- `DashboardService` — dashboard stats + caching
- `StorageService` — disk usage calculation
- `RichTextSanitizer` — strip dangerous HTML/CSS/scripts from love-letter content

**Observers** (cache invalidation):
- `DashboardCacheObserver` flushes per-user cache when memories or love letters change

## Key Conventions

**Authorization on Every Request**:
Controllers must call `$this->authorize()` at the start of every action, even list views. The policy layer ensures ownership is checked.

**Query Scopes for Filtering**:
Use scopes like `->search($term)`, `->sort('oldest')`, `->favorited()` instead of building filters in controllers. Keep query logic in Models.

**Private Storage, Authorized Serving**:
- All user uploads (memory photos, avatars) are stored on the `private` filesystem disk at `storage/app/private/`
- The `public` filesystem disk and `public/storage` symlink are configured in `config/filesystems.php` but are not used by the app
- Files are **never exposed via a public URL** — they are served through authorized controller routes:
  - `GET /memories/{memory}/image` → `MemoryController@image` (owner-only, guarded by the `Memory` policy)
  - `GET /users/{user}/avatar` → `ProfileController@avatar` (owner-only)
  - Both use `Storage::disk('private')->response(...)` to stream the file only after authorization passes
- To download a memory photo: `GalleryController@download` with same auth check

**Form Request Validation**:
- All POST/PUT forms must validate through a Form Request class
- Form Requests live in `app/Http/Requests/`
- Authorization is checked in the controller, validation rules in the Form Request

**Resource Cleanup on Failure**:
If you upload a file before creating a database record, wrap the model creation in try/catch and delete the uploaded file if creation fails. See `MemoryController@store` for the pattern.

**Dependency Injection**:
- Controllers use constructor injection for services
- Use `readonly` property modifier when the service is not reassigned

**Pagination & Query Strings**:
Use `->paginate(10)->withQueryString()` to preserve filters (search, sort, favorites) across page navigation.

**Throttling**:
- Login: 10 attempts per minute
- Register: 5 attempts per minute  
- Instant search: 30 requests per minute

**Testing Patterns**:
- All tests inherit from `Tests\TestCase`
- Feature tests use `RefreshDatabase` to rebuild the database for each test
- Test file storage with `Storage::fake('private')`
- Create fixtures with factories: `User::factory()->create()`
- Assert auth state with `$this->assertAuthenticatedAs($user)` and `$this->assertGuest()`
- Expect redirects: `->assertRedirect('/dashboard')`
- Expect database state: `->assertDatabaseHas('users', ['email' => ...])`
- Expect session errors: `->assertSessionHasErrors(['name', 'email'])`

## Frontend

**Asset Strategy**: Blade templates load **committed static assets** from `public/css/` and `public/js/` using Laravel's `asset()` helper. Each page links its own CSS files (e.g., `{{ asset('css/base.css') }}`, `{{ asset('css/dashboard.css') }}`) and page-specific JS files.

**Vite Configuration**: Vite is configured in `vite.config.js` with `@tailwindcss/vite` and `npm run build` can generate assets to `public/build/`, but the current Blade templates do not use `@vite` directives and do not reference `public/build`. This allows flexibility for future migration to Vite-driven asset serving if needed.

**CSS Framework**: Tailwind CSS v4 (no component library — all custom)

**JavaScript**: Vanilla JS (no Vue/React) for:
- Lightbox + infinite scroll on gallery
- AJAX toggles (favorites, pin letters)
- Instant search suggestions with keyboard navigation
- Calendar day detail fetch
- Dark mode toggle persistence

**Resource Files**: 
- `resources/css/app.css` — Tailwind configuration (currently not served directly)
- `resources/js/app.js` — empty placeholder
- Production CSS/JS are the committed files in `public/css/` and `public/js/`

## Testing Coverage

The test suite covers:
- **Authentication**: login, logout, register, roles, throttling
- **Memories**: CRUD, image upload/replace/delete, search, sorting, favorites
- **Gallery**: photo-only filtering, authorized download
- **Love Letters**: CRUD, mood validation, pinning, XSS sanitizing
- **Timeline & Calendar**: grouping, month view
- **Search**: global + instant, user-scoped results
- **Profile**: avatar, profile fields, password change
- **Settings**: theme toggle, account deletion
- **Security**: private image access, IDOR prevention, cross-user access control
- **Error Pages**: 403, 404, 419, 429, 500, 503 rendering

Run `php artisan test` to see coverage (currently 87 tests, 290+ assertions).

## Database Schema

**Key Indexes**:
- `(user_id, memory_date)` on memories — for per-user timeline/calendar queries
- `(user_id, letter_date)` on love_letters — same reason

**Model Relationships**:
- `User hasMany Memory`
- `User hasMany LoveLetter`
- `User hasOne UserSettings`
- `Memory hasMany Favorite` (user can favorite multiple memories)
- `LoveLetter` is single-user only

**Lifecycle**:
- On account deletion → cascade delete all memories, letters, favorites, and associated files
- On memory deletion → delete associated image file (only after DB delete succeeds)
- On avatar replacement → delete old avatar file (only after new one is stored)

## Environment Variables

See `.env.example` for the full list. Notable ones:
- `DB_CONNECTION=sqlite` — SQLite is the default; change for other databases
- `DB_BUSY_TIMEOUT=5000` — SQLite lock wait (ms) before returning a busy error
- `SESSION_DRIVER=database` — sessions stored in DB
- `CACHE_STORE=database` — cache used for rate limiting and dashboard stats
- `QUEUE_CONNECTION=database` — jobs stored in DB
- `BCRYPT_ROUNDS=12` — password hashing cost (set to 4 in tests for speed)

## Common Tasks

**Add a new memory-related feature**:
1. Create migration if schema changes needed
2. Add route in `routes/web.php` under the auth group
3. Create controller action with `$this->authorize()` at the top
4. Create Form Request if input validation needed
5. Delegate business logic to a Service
6. Create Blade template in `resources/views/`
7. Add tests in `tests/Feature/` covering auth, validation, and IDOR

**Add a new policy check**:
1. Add method to the relevant Policy (`MemoryPolicy`, `LoveLetterPolicy`)
2. Call `$this->authorize()` in the controller action *before* accessing the resource
3. Test the policy with `$this->actingAs($user)->...` in feature tests

**Handle uploaded files**:
1. Use `MemoryImageService` or `ProfileService` to store/delete
2. Wrap in try/catch and clean up on failure
3. Never expose file paths directly — always serve through authorized routes

**Add a search feature**:
1. Add a query scope to the Model (e.g., `Memory::query()->search($term)`)
2. Call the scope in the controller before paginating
3. Test with `$this->get('/memories?search=...')->assertSee()`

## Audit & Security Findings

See `AUDIT_FINDINGS.md` for any known issues or hardening steps taken.
