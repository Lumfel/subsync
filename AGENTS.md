# AGENTS.md

SubSync — Laravel 13 HOA/community platform (admin / officer / resident portals, Blade + hand-written CSS).
`CLAUDE.md` is the long-form feature catalogue; `DEPLOYMENT.md` covers production; `PLANNED_CHANGES.md`
holds the current backlog status board. `README.md` is stock Laravel.

## Commands

- Tests: `composer test` (clears config, then `php artisan test`). Single file: `php artisan test tests/Feature/X.php`; method: `--filter=name`.
- Live smoke (real tunnel): `$env:NGROK_TEST_URL='https://preschool-yearling-washout.ngrok-free.dev'; composer test:ngrok` — skipped unless `NGROK_TEST_URL` is set; optional `NGROK_{ADMIN,RESIDENT,OFFICER}_EMAIL/PASSWORD`.
- Format: `vendor/bin/pint --dirty`. There is **no** `composer run format`/`analyse` script and no static analysis. No CI.
- Services: `start.cmd` / `stop.cmd` wrap `scripts/start-subsync.ps1` (idempotent; `-Status` to check). App is served by Laragon Apache at `http://subsync.test`; public via ngrok (`https://pre-school-…ngrok-free.dev`). `composer run dev` runs only queue + scheduler + Reverb + Vite.
- Logs: read `storage/logs/laravel.log` — `php artisan pail` needs pcntl, unavailable on Windows.
- Reverb load test: `node scripts/reverb-loadtest.cjs [connections] [events]` (reads `.env`, needs Reverb running).

## Never

- **Never run `php artisan config:cache`.** `phpunit.xml` force-overrides (`DB_CONNECTION=sqlite`, `:memory:`, array session/cache) get ignored and `RefreshDatabase` would wipe the real MySQL `subsync` DB. `composer test` exists precisely to clear config first.
- **Never run `php artisan serve`** — the Windows built-in server crashes on the 2nd request.
- Don't trust `.env.example` (real `.env`: MySQL `subsync` root/blank, `SESSION_DRIVER=file`, `CACHE_STORE=file`, `BROADCAST_CONNECTION=reverb`, `QUEUE_CONNECTION=database`).
- Don't edit `_ide_helper*.php` (generated). Don't `git checkout` broadly — the tree intentionally carries many uncommitted changes.

## Auth / sessions

- Guards `admin` / `officer` / `resident` (config/auth.php); aliases `admin.auth`, `officer.auth`, `portal.role:admin,officer,resident`.
- Per-portal session cookies (`subsync_{admin|officer|resident|shared}_session`) are picked by the global `ResolvePortalSession` middleware (before `StartSession`) from: `X-Portal` header → `?portal=` → `portal`/`role` input → path → Referer → `shared`. `public/js/portal-context.js` sends the header on every page automatically; new fetch/SSE code must keep a portal hint or it silently hits the wrong session.
- `ResolvePortalSession` runs **before** `EncryptCookies`, so any cookie it reads must be excluded from encryption — `subsync_portal_hint` is (`encryptCookies(except: …)` in `bootstrap/app.php`). An encrypted hint silently falls back to `resident` and breaks the officer Google login with `InvalidStateException`.
- Logins: `/login` (resident/officer), `/admin-access` (admin, throttled 5/min), Google OAuth for residents/officers only. The Google redirect URI is host-aware at runtime — the host must be registered in the Google Console (ngrok URI is; `subsync.test` is not).
- `tests/Feature/RouteAuthAuditTest.php` fails if a route loses its guard; add new public routes to its whitelist explicitly.

## Routing / DB

- **All routes live in `routes/web.php`** (no `api.php`); `/api/*` grouped by `portal.role`. Numeric route patterns (`Route::pattern('id', …)`) make bad ids 404 instead of 500.
- Scheduled tasks in `routes/console.php` (backup 02:00 keeps 14, monthly dues, archiving). Commands in `app/Console/Commands`.
- Migrations must be rollback-safe. Verify with a scratch SQLite file (never touches MySQL): `$env:DB_CONNECTION='sqlite'; $env:DB_DATABASE="$env:TEMP\subsync-scratch.sqlite"; php artisan migrate --force; php artisan migrate:rollback --force`.

## Frontend

- CSS/JS in `public/` are plain files served with `?v=filemtime(...)` — no build step. A **design kit is active alongside them**: Tailwind v4 + daisyUI are bundled by Vite (`resources/css/app.css` imported from `resources/js/app.js`, loaded by the three portal layouts). Kit classes are deliberately namespaced — `d-*` components, `tw-*` utilities — with **no Preflight** and usage-based emission (unused kit CSS never ships). Canonical tokens + the shared component scale live at the END of `ui-polish.css` (loaded last, wins the cascade); reference page: `/design-system` (admin only).
- Icons: `<x-icon name="…" size="16"/>` (`resources/views/components/icon.blade.php`); in raw JS use `SubUI.icon(name, size)` (mirror in `public/js/ui.js`). The Blade template must stay a single line (it compiles inside inline JS strings); never put icons inside `<option>` — option content is text-only.
- Realtime is WebSocket-first (Reverb/Echo) with SSE fallback (`/api/stream/updates`, fetch-based polyfill in `ui.js`). Observed models (`SsePublishingObserver` + `$sseModels`) publish compact events on the public `subsync` channel: `announcement`, `issue`, `policy`, `hoa-expense`, `message` (senderId/senderName — never content), `resident`/`officer` (approvals: name + status), `payment` (residentId + status), `recommendation`, `event`/`rsvp`, `officer-file`. Add a model to `$sseModels` + a payload case here, then a `.listen()` + refresh in each portal script. Don't put staff-only data on the bus without switching to private channels.
- `FrontendRegressionTest` asserts exact ids/function names/markers in `public/js|css` — update those tests when renaming.

## Environment (Windows / Laragon)

- PHP must stay 8.4.x (Symfony 8 vendor); Apache uses `C:\laragon\bin\php\php-8.4.25-…`. php.ini has `curl.cainfo`/`openssl.cafile` → `C:\laragon\etc\ssl\cacert.pem`. Keep `opcache.jit=disable` (crashes Apache) and upload limits 64M/128M/512M.
- Outbound HTTPS in app code resolves its CA via `App\Support\Tls::caBundle()` (env `SSL_CA_BUNDLE` overrides). A missing CA bundle breaks Google sign-in with a generic message — check `storage/logs/laravel.log` first.
- `trustProxies('*')` is required (ngrok). `public/.htaccess` denies dotfiles/dangerous extensions and adds security headers; authorization itself always lives in middleware.
- Ops artifacts (not app code): `scripts/`, `start.cmd`, `stop.cmd`, `subsync.sql`, `households.sql`, `check_indexes.php`, root `*.md` notes besides CLAUDE.md.
