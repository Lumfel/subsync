# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Workflow Rules

- **Run the test suite after every change.** Execute `php artisan test` after any code
  edit and report the result. Do not consider a change complete until tests pass.
  If a change adds a feature, add/extend a test for it in the same change.
- **NEVER run `php artisan config:cache` in this project.** The app runs on MySQL, but tests
  rely on the `phpunit.xml` env overrides (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`,
  `SESSION_DRIVER=array`, `CACHE_STORE=array`, force-overridden); a cached config ignores them,
  so `RefreshDatabase` would run against the **real** MySQL `subsync` database and wipe it.
  Keep config uncached (`php artisan config:clear`) before running tests.

## Development Commands

### Setup
```bash
# Install dependencies
composer install
npm install

# Copy environment file and generate key
cp .env.example .env
php artisan key:generate

# Create the MySQL database and run migrations
# mysql -u root -e "CREATE DATABASE subsync CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate

# Optional: Seed with sample data
php artisan db:seed
```

### Development Server
```bash
# Start everything that's missing (MySQL, Apache, ngrok, Reverb, queue, scheduler) —
# idempotent; see also stop.cmd and: scripts/start-subsync.ps1 -Status
start.cmd

# Or start the background processes only:
composer run dev
# This runs: queue:work + schedule:work + reverb:start + Vite (NO web server)
# The app is served by Laragon Apache at http://subsync.test (public via ngrok).

# DO NOT run php artisan serve — the Windows built-in server crashes on the 2nd request.
php artisan queue:work         # Queue processor (database driver)
npm run dev                    # Vite dev server (Hot Module Replacement)
# Logs: tail storage/logs/laravel.log — php artisan pail needs pcntl (unavailable on Windows).
```

### Testing
```bash
# Run all tests
composer test

# Run specific test suite
php artisan test --filter=Feature
php artisan test --filter=Unit

# Run single test
php artisan test tests/Feature/ExampleTest.php

# Run tests with coverage
php artisan test --coverage

# Run a specific test method
php artisan test --filter=test_method_name
```

### Code Quality
```bash
# Run Laravel Pint (code formatter) — there is no composer script for it
vendor/bin/pint --dirty

# No static-analysis tool is configured in this repo.

# Generate IDE helper for better autocomplete
php artisan ide-helper:generate
```

### Database
```bash
# Reset and re-seed database
php artisan migrate:fresh --seed

# Create migration
php artisan make:migration create_table_name

# Create model
php artisan make:model ModelName

# Create seeder
php artisan make:seeder SeederName

# Create factory
php artisan make:factory FactoryName --model=ModelName

# Refresh database with seeders
php artisan migrate:refresh --seed
```

### Backups
```bash
# Create a backup now (also scheduled daily at 02:00 via schedule:work; keeps the last 14)
php artisan subsync:backup-database

# Files land in storage/app/backups/backup-YYYYmmdd-HHMMSS.sql (MySQL) or .sqlite (SQLite)
# Admin can also run/list backups from Settings → System (GET /api/backups, POST /api/backups/run)

# Restore (MySQL) — the dump contains CREATE DATABASE/USE `subsync`, so import it with:
#   mysql -u root < storage/app/backups/backup-YYYYmmdd-HHMMSS.sql
# To restore under a different name (e.g. drill/testing), strip the CREATE DATABASE/USE lines:
#   Get-Content dump.sql | Where-Object { $_ -notmatch '^(CREATE DATABASE|USE )' } | mysql -u root scratch_db
```

### Queue & Workers
```bash
# Start queue worker (database connection)
php artisan queue:work --tries=3

# Start queue worker with timeout
php artisan queue:work --timeout=90

# Process failed jobs
php artisan queue:retry all
php artisan queue:flush

# Monitor failed jobs
php artisan queue:failed
```

### Cache
```bash
# Clear application cache
php artisan cache:clear

# Clear route cache
php artisan route:clear

# Clear config cache
php artisan config:clear

# Clear view cache
php artisan view:clear

# Clear all caches
php artisan optimize:clear
```

## Code Architecture

### Laravel Structure
This is a Laravel 13 application implementing a community management system (Subsync) with multi-role authentication:

- **App/Http/Controllers**: HTTP controllers handling requests (separated by role where appropriate)
- **App/Models**: Eloquent models representing database tables
- **Routes/web.php**: Web routes defining application endpoints
- **Resources/Views**: Blade templates for rendering HTML
- **Database/Migrations**: Database schema version control
- **Database/Seeders**: Sample data for development/testing
- **App/Http/Middleware**: Request filtering and authentication guards
- **config/auth.php**: Custom authentication guards configuration
- **resources/views/layouts/**: Shared Blade layouts for different user types

### Authentication System
Custom multi-guard authentication system with three distinct user types:

1. **Admin** (`admin` guard): Full system access
   - Model: `App\Models\Admin`
   - Login route: `/admin/login`
   - Middleware: `admin.auth`
   - Dashboard: `/dashboard`

2. **Officer** (`officer` guard): Community management staff
   - Model: `App\Models\Officer`
   - Login route: `/login` (with role=officer)
   - Middleware: `officer.auth`
   - Portal: `/officer-portal`

3. **Resident** (`resident` guard): Community members
   - Model: `App\Models\Resident`
   - Login route: `/login` (default role=resident)
   - Middleware: `auth:resident`
   - Portal: `/residents`

Authentication logic resides in `App\Http\Controllers\AuthController.php` with separate methods for each user type login.

### Key Features Implemented

1. **Authentication System**: Custom guards for admins, officers, and residents with approval workflow
2. **Announcements**: CRUD functionality with archiving and view tracking
3. **Financial Management**: Tracking dues, payments, and financial reports
4. **Issue Tracking**: Reporting and managing community issues with categorization
5. **Messaging**: Real-time communication between users (residents ↔ admins/officers)
6. **File Management**: Document storage for officers and residents
7. **Dashboard**: Role-specific dashboards with consolidated data
8. **Mapping**: Geographic visualization of households and facilities using Leaflet
9. **Recommendations**: System for residents to suggest improvements
10. **Household Management**: Family and resident relationship tracking
11. **HOA Expenses Transparency**: Residents can view how HOA fees are being utilized
12. **Alert System**: Notification system for admins, officers, and residents
13. **Document Issuance**: Staff issue template-based certificates/reports for residents
14. **Event Broadcasting**: Real-time updates for HOA expenses via WebSocket
15. **Database Backups**: nightly `subsync:backup-database` (mysqldump, keeps last 14) + admin "Back up now"/list in Settings
16. **Activity Log**: append-only audit trail (announcements, finance, templates, documents, accounts) — admin-only in Settings
17. **Emergency Reports**: one-tap resident emergency issue → Critical priority + instant staff alert; camera capture on issue photos

### Common Patterns

- **Authentication**: Custom middleware (`AdminAuth.php`, `OfficerAuth.php`, `PortalRole.php`) for role-based access control
- **Validation**: Form requests used in controllers for input validation (check controller methods)
- **Eloquent Relationships**: Extensive model relationships (see models for `hasMany`, `belongsTo`, `hasManyThrough`)
- **Resource Controllers**: Standard RESTful controllers for most entities
- **Blade Templating**: Layout-based views with shared layouts in `resources/views/layouts/`
- **API Design**: JSON API endpoints under `/api` prefix for frontend consumption
- **Database Transactions**: Used in seeders and critical operations
- **Event Broadcasting**: Real-time features using Laravel events and broadcasting
- **Template System**: Report templates with variable substitution for dynamic content generation

### Environment
- Uses MySQL 8 for development (Laragon; database `subsync`, user `root`, blank password).
  SQLite remains as a fallback at `database/database.sqlite` (switch `.env` back to
  `DB_CONNECTION=sqlite`).
- Tests always run on SQLite `:memory:` via `phpunit.xml` (force-overridden), regardless of the app DB.
- **Keep the whole PHP stack on the same version (currently 8.4.25)** — CLI, background services
  (`scripts/start-subsync.ps1`), and Apache. Apache loads PHP via `C:\laragon\etc\apache2\mod_php.conf`.
  When changing PHP versions you must:
  1. Copy the matching `php8ts.dll` **and all PHP runtime DLLs** (`libcurl` deps, `icu*.dll`, `libsodium`,
     `nghttp2`, `libssh2`, …) from the PHP folder into `C:\laragon\bin\apache\httpd-*\bin\`.
     Stale copies cause "Entry Point Not Found" dialogs, `curl`/`intl` load failures, and
     `0xC0000005` access-violation crashes of the Apache child process.
  2. Enable `extension=pdo_mysql` in the new php.ini.
  3. Keep **JIT disabled** (`opcache.jit=disable`, `opcache.jit_buffer_size=0`) — PHP 8.4 JIT under
     mod_php on Windows crashes the Apache child roughly every 3rd request.
  4. Restore sane upload limits — the 8.4.25 defaults are tiny (`upload_max_filesize=2M`,
     `post_max_size=8M`, `memory_limit=128M`). Keep `64M` / `128M` / `512M` respectively.
  Symfony 8 vendor packages require PHP >= 8.4 — a PHP 8.3 web server breaks error rendering and some endpoints.
  Apache crash history is logged at `C:\laragon\bin\apache\httpd-*\logs\error_log` (watch for `AH00428 … 3221225477`).
- Environment variables managed through `.env` file (see `.env.example`)
- Queue system configured for database jobs (`QUEUE_CONNECTION=database`)
- Broadcasting: Laravel Reverb (`BROADCAST_CONNECTION=reverb`) with SSE fallback at `/api/stream/updates`
- Filesystem storage: local (public/storage)
- Cache: file driver (`CACHE_STORE=file`)
- Session: file driver (`SESSION_DRIVER=file`)

### API Design
The application follows a RESTful API design pattern:
- API routes are prefixed with `/api`
- Resource controllers handle standard CRUD operations
- Custom endpoints for specific functionality (e.g., `/api/dashboard-data` combines multiple queries)
- Response format is JSON with consistent structure
- Authentication via Laravel Sanctum or session guards depending on client

### Performance Considerations
- The dashboard endpoint (`/api/dashboard-data`) combines 11+ parallel calls into a single endpoint
- Eloquent eager loading used to prevent N+1 query problems
- Selective column selection minimizes data transfer
- Database sessions and caching for improved performance
- Queue system for background processing (notifications, reports, etc.)

### Frontend Assets
- Built with Vite and Tailwind CSS
- Development: `npm run dev` (Hot Module Replacement)
- Production: `npm run build`
- Asset compilation managed via `vite.config.js` and Laravel Vite Plugin

## Testing Conventions
- Feature tests in `tests/Feature/` directory (HTTP endpoint testing)
- Unit tests in `tests/Unit/` directory (isolated class/method testing)
- Uses Laravel's built-in testing utilities with PHPUnit
- Tests database transactions with `RefreshDatabase` trait where needed
- Mocking facilities available via Mockery and Laravel's Bus::fake(), Storage::fake(), etc.

## Important Files to Review
- `routes/web.php` - Understand application routes and controller mappings
- `app/Http/Controllers/` - Review controller logic for business rules and validation
- `app/Models/` - Understand data structure, relationships, and mutators/casts
- `database/migrations/` - Review database schema evolution and indexing
- `database/seeders/` - See sample data structure and relationships
- `resources/views/layouts/` - Shared Blade templates and layout structure
- `app/Http/Middleware/` - Custom authentication guard implementations
- `config/auth.php` - Authentication guard and provider configuration
- `app/Events/` and `app/Listeners/` - Event-driven architecture (if applicable)
- `app/Jobs/` - Queued jobs for background processing

## Laravel Boost
This repository has Laravel Boost installed for AI-assisted development:
- Provides 15+ tools and skills for agents like Claude Code
- Installed via `composer require laravel/boost --dev`
- Configured with `php artisan boost:install`
- Enables AI-powered code generation, analysis, and debugging capabilities

## Troubleshooting
- **Login issues**: Check that users have correct status ('Active' not 'Pending') in seeders
- **Migration errors**: Ensure the MySQL server is running (Laragon) and the `subsync` database exists; SQLite fallback is `database/database.sqlite`
- **Queue not processing**: Verify `QUEUE_CONNECTION=database` in .env and run `php artisan queue:work`
- **Assets not updating**: Clear Vite cache with `rm -rf node_modules/.vite` or check vite.config.js
- **Authentication guard errors**: Verify guards are properly defined in config/auth.php
- **CSRF token mismatch**: Ensure web routes use web middleware group or exclude from CSRF when needed for API
- **Broadcasting issues**: Check BROADCAST_DRIVER in .env (currently set to log, can be changed to pusher/redis)
- **Real-time features**: Ensure WebSocket server is running if using Pusher/Redis for broadcasting

## Specific Development Tips for Subsync

### Working with Multi-Guard Authentication
When developing features that need to check authentication for specific roles:
- Use middleware: `portal.role:admin,officer,resident` for API routes
- Check session data: `session('active_role')` to determine current user type
- Access authenticated user: `Auth::guard('resident')->user()` etc.

### Optimized Data Loading
The `/api/dashboard-data` endpoint demonstrates optimized data fetching:
- Uses eager loading (`with()`) to prevent N+1 queries
- Selects only needed columns with `select()`
- Maps collections to minimize data transfer
- Combines multiple related queries into single endpoint

### File Storage Patterns
Officer and resident file management follows this pattern:
- Files stored in `storage/app/public/{category}`
- Use Laravel's Storage facade with `Storage::disk('public')`
- Files are linked via `php artisan storage:link`

### Real-time Features
Real-time runs on Laravel Reverb (WebSocket, `BROADCAST_CONNECTION=reverb`) with an SSE
fallback. Model observers write compact events to `sse_events` AND broadcast
`App\Events\SubSyncEvent` on the public `subsync` channel (announcement, issue, policy,
hoa-expense, message). Portal JS treats the socket as primary and only starts SSE
(`/api/stream/updates`) when Reverb is unavailable. Keep staff-only data off the public
channel unless you move to private channels + broadcasting auth.

## Hardening session — 2026-10-20 (autonomous)

Status board for the 13-item backlog lives in `PLANNED_CHANGES.md`. Headlines:
- Shared `public/js/portal-context.js` now always sends `X-Portal` + ngrok header
  (the old per-layout inline patch silently no-oped when `APP_BASE` was empty);
  `tests/Feature/RouteAuthAuditTest.php` guards route auth + that script.
- Icons: `<x-icon>` Blade component (40 monochrome line icons); 128 emoji UI icons
  replaced across 7 views; the template renders on ONE line because it is compiled
  into inline JS strings.
- Realtime: `Message` events added to the bus; WS-first with SSE fallback in all
  three portals; verified end-to-end (node ws client) and load-tested —
  `scripts/reverb-loadtest.cjs` (200 connections → 100% delivery, ~10 ms).
- Google OAuth: host-aware redirect URI (`route('google.callback')`), failures are
  logged (`report($e)`), and `App\Support\Tls::caBundle()` gives Guzzle a CA bundle
  (Laragon ships none — every outbound HTTPS call used to fail with SSL error 60).
- Login/registration page: hidden role fields are disabled (they blocked form
  submission with "An invalid form control … is not focusable").
- Migrations: 4 `down()` methods fixed to drop indexes/uniques before columns
  (SQLite rollback smoke: migrate → rollback → migrate is clean).
- `public/.htaccess`: dotfiles + dangerous extensions denied, X-Frame-Options and
  Referrer-Policy added (auth itself stays in middleware).

### UI/UX design system (same session, later pass)

- **Tokens + canonical component scale** appended to the end of `public/css/ui-polish.css`
  (`--space-*`, `--radius-*`, `--shadow-*`, `--t-fast/--t-med`; normalized `.btn`/`.card`/
  inputs/pills/modals/popovers via doubled selectors so they win the cascade). Colors and
  font sizes intentionally untouched.
- **Tailwind v4 + daisyUI kit activated**: `resources/css/app.css` imports theme + utilities
  only (NO Preflight), daisyUI is prefixed `d-`, utilities `tw-`, themed to the SubSync
  palette; emission is usage-based (a page pays only for kit classes it uses). Wired via
  `resources/js/app.js` → `npm run build` and the portal layouts pick it up through @vite.
- **`/design-system`** (admin only) documents tokens, components and the icon set; guarded
  by `RouteAuthAuditTest` and covered by a FrontendRegression assertion.

### Database Relationships
Key relationships to understand:
- Resident belongsTo Household
- Household hasMany Resident
- Household belongsTo Family
- FinancialRecord belongsTo Resident
- IssueReport belongsTo Resident
- Conversation hasMany Message through ConvParticipant
- HoaExpense: Standalone model for tracking HOA expenditures
- ReportTemplate belongsTo many Reports through resident
- Report belongsTo Resident and ReportTemplate

### Working with Reports and Templates
- Reports/Documents are a **staff-only** feature (admin + officer); residents have no reports UI or API access
- Report templates are managed via `/api/report-templates` (admin/officer only)
- Staff issue certificates/documents for a resident via `/api/reports/issue`; issued documents are listed via `/api/reports` (staff only)
- Printable issued document: `/reports/{id}/print` (staff only)
- Templates use `{{{variable_name}}}` syntax for variable substitution

### HOA Expenses Transparency
- New feature allowing residents to view how HOA fees are utilized
- Accessible via `/api/hoa-expenses` (resident only)
- Expenses are broadcast in real-time when created/updated/deleted
- Sample data includes landscaping, pool maintenance, security, insurance, and utilities