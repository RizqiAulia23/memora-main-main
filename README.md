# Memorify

Memorify is a private, couples-focused memory companion built with Laravel. It helps partners preserve and relive the moments that matter — through a photo memory journal, a photo gallery, love letters, an interactive timeline, a memory calendar, and a fast global search — all in one cozy, dark-mode-ready dashboard.

Built with Laravel 13, Blade templates, vanilla JavaScript, Tailwind CSS, and Vite, Memorify keeps every uploaded photo private by default and enforces authorization on every image request.

## Features

### Authentication
- **Register** — create a free account with a default `user` role.
- **Login** — session-based authentication with optional "remember me".
- **Logout** — server-side session invalidation.
- **Roles** — `user` (default) and `admin` (`role` column on the `users` table).

### Memories

- **CRUD** — create, read, update, and delete memory entries.
- **Image upload** — one photo per memory, stored privately (jpg, jpeg, png, webp, max 2 MB).
- **Search** — filter by title or description.
- **Sorting** — newest, oldest, or by memory date.
- **Favorites** — toggle favorited memories with an inline heart button (AJAX).
- **Pagination** — 10 items per page with query-string-aware pagination.

### Gallery

- **Photo gallery** — only memories that have a photo are shown, newest first.
- **Private image serving** — images are streamed through an authorized controller route, never exposed via a public URL.
- **Authorized download** — download with a friendly filename, guarded by the same policy.
- **Lightbox + keyboard navigation** with infinite scroll on the gallery grid.

### Love Letters

- **CRUD** — write, read, update, and delete letters.
- **Rich text** — HTML content that is sanitized (scripts, event handlers, and dangerous CSS are stripped).
- **Mood** — pick from a set of moods (happy, love, romantic, nostalgic, grateful, thoughtful, missing).
- **Pin** — pin a letter to the top (AJAX toggle).
- **Search** — love letters are included in global search results.
- **Pagination** — 10 letters per page, pinned first, newest first, with query strings.

### Timeline

- A date-based memory timeline grouped by year and month, with year navigation pills.

### Calendar

- Monthly calendar grid highlighting days that contain memories.
- Day detail view fetched via AJAX when you select a highlighted day.

### Search

- **Global search** — search across memories, photos, and love letters.
- **Instant search** — live suggestions from the topbar as you type (debounced, throttled endpoint).
- **Keyboard navigation** — arrow up/down to move through results, Enter to open, Escape to close.
- **User-scoped results** — results are always filtered to the signed-in user.

### Profile

- **Avatar** — upload and remove an avatar (stored privately).
- **Bio**, **partner name**, **relationship date**, **location**.
- **Password** — change password with current-password verification.

### Settings

- **Dark mode** — global theme toggle that persists per user across all pages.
- **Notifications** — enable or disable notifications.
- **Storage usage** — live disk usage of your private uploads.
- **Account deletion** — delete account with password confirmation (cleans up files).

### UI/UX

- **Responsive UI** — full layouts that work on mobile and desktop.
- **Empty states** — friendly, actionable empty state for every collection.
- **Toasts** — success and error notifications.
- **Loading states** — spinners for instant search, gallery, and calendar loads.
- **Dark mode** — theme is applied per user and stored in `user_settings`.
- **Custom error pages** — styled pages for 403, 404, 419, 429, 500, and 503.
- **Marketing pages** — public Home, About, Features, and Contact pages.

---

## Security

Memorify follows straightforward, practical security practices:

- **Private storage** — all uploads (memory photos and avatars) are stored under `storage/app/private` and are never exposed through a public URL.
- **Authorization policies** — ownership rules live in `MemoryPolicy` and `LoveLetterPolicy`; every read/write checks that the authenticated user owns the resource.
- **IDOR protection** — route-model-bound resources reject requests to other users' data with `403`; missing resources return `404`, so nothing leaks.
- **CSRF** — Laravel's built-in CSRF protection protects every state-changing form.
- **Validation** — Form Requests validate input server-side (auth, profile, settings, memories, letters) before storage.
- **XSS sanitization** — love-letter content is passed through a sanitizer that strips `<script>`, inline event handlers, `javascript:` URLs, and dangerous CSS such as `url()`, before it is stored.
- **Rate limiting** — login (10/min), registration (5/min), and instant search (30/min) endpoints are throttled.
- **Password hashing** — passwords are stored using Laravel's default `hashed` cast (bcrypt).
- **File cleanup** — deleting a memory removes its photo, replacing a photo deletes the old file, removing an avatar deletes the file, and deleting an account removes all related files.

---

## Storage

Uploaded files use **private storage**, not public storage.

```
storage/
└── app/
    └── private/     <-- memory photos and avatars live here
```

- Files are stored on the `private` filesystem disk.
- They **cannot** be accessed directly through a public URL (there is no `public/storage` symlink for user uploads, and files are not visible under `public/`).
- Images are served through **authorized controller routes**:
  - `GET /memories/{memory}/image` → `MemoryController@image` (owner-only, guarded by the `Memory` policy).
  - `GET /users/{user}/avatar` → `ProfileController@avatar` (owner-only).
  - Both use `Storage::disk('private')->response(...)` to stream the file only after authorization passes.

### Legacy public files

If you had uploaded files under `storage/app/public` in an earlier version and want to keep them, move them from:

```
storage/app/public/<path>
```

into the matching location under:

```
storage/app/private/<path>
```

The database already stores relative paths (e.g. `memories/photo.png`, `avatars/me.png`), so a file moved into `storage/app/private/memories/photo.png` will be served from the authorized route since the app reads the same relative path.

> **Note:** do not keep user uploads in the public disk — files there are served without any authorization checks.

---

## Installation

Prerequisites: PHP 8.3+, Composer, Node.js + npm.

Clone the repository and install dependencies:

```bash
git clone <repository-url>
cd memora-main-main
composer install
```

Copy the environment file and generate an application key:

```bash
copy .env.example .env   # Windows
cp .env.example .env     # Linux / macOS

php artisan key:generate
```

Set up the database and run migrations:

SQLite uses a file at `database/database.sqlite` by default. If the file doesn't exist yet, create it first:

```bash
# PowerShell / Windows
New-Item -ItemType File -Force database\database.sqlite

# Linux / macOS
touch database/database.sqlite
```

Then migrate (and seed demo data if you want a filled-in account):

```bash
php artisan migrate
# optional: frontend assets
npm install
npm run build
php artisan serve
```

`php artisan serve` starts the app at `http://localhost:8000`.

> If you'd prefer a one-command setup on a fresh clone, `composer setup` runs install, copies `.env`, generates a key, migrates, and builds assets for you (without seeding).

---

## Database

- **Default driver**: SQLite (`DB_CONNECTION=sqlite`) with the file at `database/database.sqlite`.
- **Migrations**: run `php artisan migrate` to create all tables:
  - `users` (+ profile fields `avatar`, `bio`, `partner_name`, `relationship_date`, `location`, and `role`)
  - `memories`, `favorites`, `love_letters`, `user_settings`
  - framework tables: `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`, `password_reset_tokens`
- **Seeding**: `php artisan db:seed --force` runs `AdminSeeder` and `DemoDataSeeder`. If demo data already exists, the seeders keep the existing data intact and never duplicate it.

### Demo accounts

After seeding, two accounts are available:

| Role  | Email               | Password  | Notes                                    |
|-------|---------------------|-----------|------------------------------------------|
| admin | `admin@memorify.com`     | `admin123`| Administrator (created by `AdminSeeder`) |
| user  | `demo@memorify.com`       | `password`| Demo couple with memories + letters      |

> **Warning**: these credentials are for local/demo use only. Any password demo data must be changed on any real deployment — never run the demo seeder against a public database.

---

## Development Commands

```bash
php artisan serve                     # Run the dev server at http://localhost:8000
php artisan migrate                   # Run pending migrations
php artisan migrate:fresh --seed      # Rebuild the database and seed it
php artisan db:seed --force           # Re-run seeders (idempotent)
php artisan optimize:clear            # Clear cached config, routes, and views
php artisan test                      # Run the whole test suite
vendor\bin\pint                       # Code style check + auto-fix (Windows)
npm run dev                          # Run Vite dev server with hot reload
npm run build                        # Build production frontend assets
```

> Use `npm run dev` when editing JavaScript/CSS during development; `npm run build` before deploying.

---

## Testing

**Current status:** 70 tests passed / 227 assertions · Pint clean.

Memorify ships with a feature/unit test suite covering:

- **authentication** — login, logout, registration, validation, roles
- **memories** — CRUD, image upload/replace/delete, search, sorting
- **favorites** — AJAX toggle, favorites page
- **gallery** — photo-only listing, authorized download
- **love letters** — CRUD, mood validation, pinning, XSS sanitizing
- **timeline** — grouping by month/year
- **calendar** — month view, date endpoint, invalid input
- **search** — global + instant, user-scoped results
- **profile** — avatar upload/remove, profile & password updates
- **settings** — theme toggle, account deletion with password
- **security** — private image & avatar access, IDOR sweep
- **error pages** — 403, 404, 419, 429, 500, 503 render the custom pages
- **authorization** — cross-user access returns `403`/`404`, no leaks
- **rate limiting** — login, register, and instant search throttling

```bash
php artisan test
```

---

## Architecture

```text
app/
├── Http/
│   ├── Controllers/      # Thin controllers for auth, memories, gallery, letters, timeline,
│   │                      #   calendar, search, profile, settings, favorites, dashboard
│   └── Requests/         # Form Request validation classes
├── Models/                # Eloquent models (User, Memory, LoveLetter, Favorite, UserSettings)
├── Services/              # Business logic (image storage, dashboard, account, sanitizer…)
├── Policies/              # Authorization (Memory, LoveLetter)
├── Observers/            # Cache invalidation on model changes
└── Enums/                 # e.g. LoveLetterMood

resources/
└── views/                # Blade templates (auth, memories, gallery, letters, timeline,
                          #   calendar, search, profile, settings, partials, errors, …)

public/
├── css/                  # Compiled + page-specific CSS
└── js/                   # Vanilla JS for per-page behavior

database/
├── migrations/          # Schema
├── factories/           # Test factory definitions
└── seeders/            # Admin + demo data seeders

tests/                  # Feature (and unit) tests
```

Highlights:

- **Controllers** are thin — they authorize, validate, and delegate heavy work to `Services`.
- **Services** (`ImageStore`, `MemoryImageService`, `ProfileService`, `AccountService`, `StorageService`, `DashboardService`, `RichTextSanitizer`) hold the reusable logic.
- **Policies** enforce ownership on every read and write, including image/avatar access.
- **Observers** (`DashboardCacheObserver`) flush per-user dashboard and storage caches when models change.

---

## Environment

The following variables are used by the app (see `.env.example` as the single source of truth — no secrets live in the repo):

| Variable                     | Default                    | Purpose                                             |
|------------------------------|----------------------------|-----------------------------------------------------|
| `APP_NAME`                   | `Laravel`                  | Application name                                    |
| `APP_ENV`                    | `local`                    | Environment (local/production)                     |
| `APP_DEBUG`                  | `true`                     | Show/hide error details                              |
| `APP_URL`                    | `http://localhost`       | Base URL used for generated links                   |
| `APP_KEY`                    | _(generated)_              | Application encryption key (`php artisan key:generate`) |
| `DB_CONNECTION`              | `sqlite`                   | Database driver (SQLite is the default)             |
| `SESSION_DRIVER`             | `database`                 | Session store (database-backed)                     |
| `CACHE_STORE`                | `database`                 | Cache store used for rate limiting + stats caches   |
| `QUEUE_CONNECTION`           | `database`                 | Queue job store (database-backed)                  |
| `BCRYPT_ROUNDS`             | `12`                       | bcrypt cost factor for password hashing             |
| `MAIL_MAILER`                | `log`                      | Log mails instead of sending (dev convenience)       |
| `FILESYSTEM_DISK`            | `local`                    | Default filesystem disk                             |

Secret values (e.g. `AWS_*`, mail passwords) are optional and only relevant if you switch providers — the project does not require them for local development.

---

## License

Memorify is a personal-project skeleton built on the Laravel framework. It is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).