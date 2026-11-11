# SubSync — Full System Review (2026-10-21)

Scope: every subsystem (auth, portals, issues, announcements, messaging, payments/ledger,
events, policies, transparency, reports, map, settings, realtime, admin tooling, scheduler).
Method: three parallel code audits (completeness, security/privacy, performance), each finding
hand-verified against the source before acting on it; industry-standard mapping and public HOA
platform benchmarks researched; the full P0 privacy/security backlog and the panel's High/Medium
revisions (S2/S5/S11 + S3/S6/S9/S13) and a backend concurrency/consistency pass were
implemented afterwards — 287 tests green.

---

## 1. Executive summary

**Strong:** the money chain (ledger → cached balance → receipts), the issue lifecycle with
audit trail/SLA, the realtime bus (WS-first with SSE fallback), role-guarded APIs,
per-portal sessions, and the scheduled automation (dues, reminders, archiving, backups)
are all real, tested, and coherent. No TODOs/stubs exist in app/ or views.

**Top risks found (and fixed during this review):** a JavaScript `const`-reassignment bug
that silently broke the resident portal's combined data load on every visit; a household
member IDOR (any resident could read/edit/delete other households' members); an
unauthenticated PII leak on the public WebSocket channel design (fixed — private
channel + SSE role filter, see P0.1); several stored-XSS sinks in list rendering; missing login throttles; and
deactivated accounts that could still sign in.

---

## 2. Fixes applied during the review

| Fix | Area | Detail |
|---|---|---|
| Resident portal data load restored | Completeness | `public/js/residents.js` reassigned `const RESIDENT_ID/HOUSE_ID/RESIDENT_NAME` inside `loadPortalData()` → TypeError on every load; the combined `/api/resident/portal-data` result was thrown away and 6 fallback requests ran instead. Now `let`. |
| Household members: admin panel fixed + IDOR closed | Security/Completeness | Routes now allow admin/officer/resident; controller scopes residents to their own `house_id` (read/create) and adds `authorizeMember()` ownership checks (update/delete). The admin dashboard's member management actually works now. |
| Officer self-rename fixed | Completeness | `OfficerController::update` allowed only admins, so the officer's editable name in the sidebar always 403'd. Officers may now edit name/contact for themselves; status/password stay admin-only. |
| Deactivated accounts rejected at login | Security | Resident/officer/admin logins and the Google callback now require `status === Active` ("deactivated" message for Inactive, waiting-for-approval for Pending). |
| Default admin seeding guarded | Security | The hardcoded `admin@subsync.com / Admin@12345` fallback now only seeds in `local`/`testing` environments. |
| Login/register throttling | Security | `throttle:10,1` added to `POST /login` and `POST /register` (admin login already had 5/min). |
| Stored-XSS sinks escaped | Security | Member cards/table, issue attachment names, officer-file name/category/notes/download attr, announcement `target`/`event_date` in all three portals now pass through `escHtml()`; the member-card `onclick` JSON also escapes quotes. |
| Announcement read-tracking wired | Completeness | Opening an announcement now POSTs `/api/announcements/{id}/view`, so the resident notification center's "unread announcements" alerts finally clear. |
| Officer conversation-delete CSRF bug | Bug | `X-CSRF-TOKEN` sent the function reference instead of calling `csrfToken()` → guaranteed 419. Fixed. |
| Performance indexes | Performance | New migration `2026_10_21_000001_add_performance_indexes`: `issue_reports(status)`, `(archived_at, created_at)`, `(priority, status)`; `residents(status)`, `officers(status)`; `financial_records(resident_id, record_date)`; `messages(conversation_id, created_at)`; `payment_submissions(status, created_at)`; `events(starts_at)`; `officer_files(archived, created_at)`; `recommendations(status, created_at)`; `hoa_policies(status, created_at)`. Applied to the live DB; rollback smoke-tested on scratch SQLite. |
| Regression tests | QA | Login gates, member scoping, officer self-update, reminder command, balance-link + heatmap markers — suite now 256 passing. |

A remaining known minor: two dead poller blocks in `admin-dashboard.js` share one guard flag
so only one ever runs (harmless dead code, cleanup listed below).

---

## 3. Remaining backlog (prioritized)

### P0 — privacy/security follow-ups (all done)
1. **DONE — private WebSocket channel + role-filtered SSE.** `SubSyncEvent` now implements
   `ShouldBroadcast` on a `PrivateChannel('subsync')`; `routes/channels.php` authorizes it via
   the `admin`/`officer`/`resident` guards (`AuthController::adminLogin` logs admins into the
   `admin` guard so `broadcasting/auth` succeeds for all three portals). Payloads are
   PII-scrubbed (`SsePublishingObserver`: no resident/officer names, no message sender names).
   `StreamController::deliverable()` applies the same role filter to the SSE fallback
   (residents see only community events, their own payments and own approvals; staff see
   staff data; officers see resident/officer/payment/issue events). Tests:
   `RealtimeBusTest` (private channel + auth for guest/resident/admin), `RealtimeMessagesTest`
   (scrubbed payload), `SseStreamTest` (role filtering).
2. **DONE — confidential uploads moved to the private disk.** Issue photos/documents,
   officer files, and HOA expense receipts now store on the `local` disk and stream through
   authorized routes (`/api/issues/media/{id}` owner-or-staff, `/api/officer-files/{id}/download`
   sender-or-admin, `/api/hoa-expenses/{id}/receipt` any signed-in portal). Legacy rows on the
   public disk still resolve (disk fallback). Fixed en route: a morph-map mismatch meant issue
   media relations never matched their stored `entity_type`, so photos/documents had never
   actually rendered via `with('media')` — `Relation::morphMap` for media-owning models fixed it.
   Tests: `PrivateMediaTest`.
3. **DONE — RA 10173 compliance basics.** Registration now requires a consent checkbox
   (client + server `accepted` rule) linking a public `/privacy` Data Privacy Notice; Settings
   gained "Download my data (JSON)" (`GET /api/settings/export`, actor-scoped) and
   "Request account erasure" (`POST /api/settings/erasure-request`, activity-logged);
   `subsync:prune-activity-logs --days=180` runs monthly to enforce retention.
   Tests: `PrivacyComplianceTest`.
4. **DONE (mostly) — password change hardening.** `POST /settings/password` is throttled
   (5/min) and the session rotates on a credential change; the response carries the fresh
   CSRF token so the settings page keeps working. Cross-session invalidation needs a
   session store that supports it (Redis/DB) — documented for production.
5. **DONE — export accountability.** Every CSV export (resident directory, issues, payments,
   HOA expenses, resident statement) writes an `export` entry to the activity trail with the
   actor and row count.

### P1 — performance at scale (items 1–2 done; rest documented for production)
1. **DONE** — `Ledger::agingForMany()` computes aging for all residents from a single query;
   `LedgerController::aging` uses it.
2. **DONE** — `EventController::index` uses `withCount` for going/maybe + one RSVP lookup for
   the resident's own statuses (was 3 queries per event).
3. Dashboard payloads embed whole tables: `/api/dashboard-data` (all residents/households/
   members), `/api/issues` (unbounded with media+responses), message histories, payment
   queues, officer files. Cap/paginate server-side (the UI already caps client-side).
4. Admin portal refetches the full `/api/dashboard-data` on every WS event; officer portal
   refetches 3 unbounded endpoints per event. Patch local state from event payloads instead.
5. SSE fallback holds an Apache worker ~5s per cycle per client and the 5s `reconnect` makes
   clients refresh continuously when WS is down — lengthen the window, refresh only on
   `sync` (fresh connect), and gate refreshes on `document.hidden`.
6. Cache invalidation is global: `DashboardCacheObserver` flushes 9 keys for 12 unrelated
   models; `resident.portal.data.{id}` is never invalidated (TTL-only). Scope keys per model;
   add the per-resident key.
7. Bulk operations (CSV import, bulk-archive, dues generation) trigger per-row observer
   writes (SSE row + synchronous Reverb publish + activity log + cache forgets). Batch with
   `withoutEvents()` + one aggregate event per operation.
8. Search uses `LIKE '%term%'` across 7 tables per keystroke — add FULLTEXT indexes or
   prefix search.
9. `stream:updates`/exports/message-send have no throttles; `trustProxies('*')` lets clients
   spoof `X-Forwarded-For` around the IP-keyed throttles outside the tunnels.

Recorded findings closed 2026-10-24 (from the flow/audit notes that were still open):
`POST /api/assessments/generate` with a malformed `period` used to 500 inside the command
(`explode('-')` on a non-date) — now validated as `YYYY-MM` (422) and the command itself
rejects bad input with a clear message. Bulk approvals/rejections and
`announcements:archive-stale` used query-builder `update()/delete()`, which skips model
events — they now iterate the models so the activity log, realtime bus and cache invalidation
fire. `subsync:backup-database` no longer keeps a half-written dump when `mysqldump` times out,
and SQLite backups use `VACUUM INTO` (consistent snapshot; a plain copy could miss WAL frames).
Tests: `BackendHardeningTest` (malformed period, bulk-approval activity).
Also: `APP_NAME` was left at Laravel, so every reminder/receipt email was branded
"Laravel" — now `SubSync` (drives mail from-name and app name; the per-portal session
cookies are set by `ResolvePortalSession`, so nothing else shifts). And `Options -Indexes`
was nested under `mod_negotiation` (so it silently disappeared if that module was off) —
moved under `mod_autoindex`; directory listings now return 403 either way.

Correctness pass applied 2026-10-24:
App timezone is now `Asia/Manila` (was hardcoded `UTC`) — Eloquent-written timestamps were 8h off
the DB-defaulted ones (`CURRENT_TIMESTAMP` tables) and every server-formatted time displayed
8 hours early for PH users; the scheduler now fires at local wall-clock times. `config/auth.php`
password broker key renamed `users` → `residents` so `Password::broker()` matches
`defaults.passwords` (was an `InvalidArgumentException` waiting for the first password-reset
call). `subsync:remind-balances` now honors the resident's "Email copies" setting, with email
opt-out by default for transactional dues reminders (`2026_10_24_000001_…`, backfills existing
rows; a resident can still disable it in Settings). The private `local` disk has `serve => false`
(its unused signed `/storage/{path}` routes are gone — private files stream through the
authorized controller routes only; the `public` disk still serves `/storage/*` via Apache).
Removed the orphaned `public/dashbaord_2.svg`. Tests: `LedgerFinanceTest` opt-out case.

Production/security hardening applied 2026-10-23:
`public/.htaccess` now sends a Content-Security-Policy (explicit allowlist: self, Google
Fonts, ui-avatars, OSM tiles, ws/wss realtime), HSTS, and Permissions-Policy;
`styles.css`/Leaflet/vendor assets are `?v=<mtime>`-versioned so the 1-year immutable cache
can't serve stale CSS/JS; the service worker (`sw.js`, cache v7) only caches successful
same-origin responses and returns a real fallback instead of `respondWith(null)`;
`issues:purge-resolved` archives on the authoritative `resolved_at` (was `updated_at`, so any
later edit reset the 30-day clock and resolved issues never archived); and
`php artisan subsync:check-prod-env` (+ a boot-time `Log::critical` in non-local debug mode)
guards the production `.env` (debug, HTTPS URL, secure/encrypted sessions, log level, mail).
Tests: `ProductionEnvTest`, `ArchiveStaleTest` (regression).

Additional backend hardening applied 2026-10-23 (concurrency/consistency pass):
`POST /api/payments/{id}/validate` runs in a transaction with `lockForUpdate` (no double
ledger credit; a validated payment can no longer be rejected with the credit silently left
in place — 422); conversation creation is atomic; `assessments:generate-dues` prefetches
already-charged residents instead of one query each; unchanged issue assignments no longer
write spurious `assign` audit entries (int comparison); the `announcements.Draft` cache key
is invalidated together with the other announcement feeds. Tests: `BackendHardeningTest`.

### P2 — left-out parts / completeness
1. **DONE — announcement templates wired** — the admin create modal already rendered DB
   templates when seeded; the officer create card now does the same (with built-in presets as
   fallback). The Templates admin UI still edits report templates only — announcement-template
   CRUD remains API/seed-level (acceptable; not panel-required).
2. **No edit UI** for budgets, events, families, report templates (PUT routes exist).
3. **DONE** — the "Security Logs" analytics card now renders the real activity trail
   (`/api/activity-logs`, filtered to export/erasure/backup/password/account events).
4. `/mapping` remains a prototype page (whitelisted, no server data) — kept for reference;
   remove or upgrade into a public read-only map if time allows.
5. **DONE** — `public/design-preview.html` and `public/finance_sample.xlsx` removed.
6. **DONE — dead admin JS removed** (~500 lines: `renderPaymentStatuses`, `renderPayments`,
   the financial-records/receipts/Excel-import cluster) plus the unused SheetJS loader in the
   layout; payments gained an in-app CSV export button instead. All tests green (268).
7. Mail is the `log` driver in dev — receipt/reminder sends are silent (fine for dev;
   production needs a real MAIL_MAILER).

---

## 4. Industry standards mapping

| Standard | Where SubSync stands | Gaps to close |
|---|---|---|
| **OWASP Top 10 (2021)** | A01: role middleware everywhere + audit test; member IDOR + officer-file authz fixed; private WS channels. A03: Blade auto-escapes; XSS sinks fixed. A07: throttles on all logins; deactivated accounts rejected. A09: activity log + issue audit trail. A05: **CSP + HSTS + Permissions-Policy headers added**, `subsync:check-prod-env` asserts production settings. | Keep `unsafe-inline` in CSP until the inline bootstrap scripts are extracted (documented). |
| **OWASP ASVS L1** | Input validation via FormRequest-style rules everywhere; no `$request->all()`; CSRF enforced; session regenerated at login **and on password change**; uploads restricted by mime/size. `subsync:check-prod-env` verifies `SESSION_SECURE_COOKIE`/`SESSION_ENCRYPT`/`LOG_LEVEL`/mail before deploy (see `DEPLOYMENT.md` → Security hardening). | Cross-session invalidation needs a shared session store (Redis/DB) — documented. |
| **WCAG 2.2 AA** | Skip-to-content + focus-visible rings, aria-live on the login message, aria-labels on icon buttons, elder-friendly tap targets/contrast pass already done. | Inputs widely use placeholders instead of `<label>`s (needs programmatic labels); audit chart/map alternative text. |
| **RA 10173 (PH Data Privacy Act)** | Role-scoped access; receipts private; CSV export formula-injection neutralized. | Consent at registration, privacy notice, data export/erasure path, retention limits — see P0.3. |
| **HOA governance (DHSUD/HLURB context)** | Dues schedule → ledger → statements; official receipts; transparency page for residents; monthly reminders; admin validation queue; audit trail. | Report an annual income/expense statement export for the general assembly; meeting minutes archive. |
| **ISO/IEC 25010 (quality model)** | Functional suitability, security, reliability (backups keep 14 + scheduler), maintainability (tests, audit docs) covered. | Performance efficiency and portability (SQLite→MySQL deployment guide exists in DEPLOYMENT.md) are documented targets; P1 list is the roadmap. |

---

## 5. Public HOA/community platforms as reference

Benchmarked feature coverage against widely deployed HOA platforms (as of 2026):

| Capability | SubSync | Typical references |
|---|---|---|
| Online dues/payments + official receipts | Yes (GCash manual validation, ledger, aging) | PayHOA, TownSq, Buildium, AppFolio |
| Community transparency (where dues go) | Yes (`/api/transparency/summary`, expenses) | "Financial transparency" modules in TownSq/PayHOA |
| Work orders / issue reports with SLA + photos + pin | Yes (auto-priority, SLA badges, map) | Buildium maintenance, AppFolio work orders |
| Announcements + templates + read tracking | Yes (templates backend-only; see P2.1) | HOA Express, TownSq |
| Direct messaging resident↔officer/admin | Yes (invite/accept, realtime) | TownSq, FrontSteps |
| Community calendar + RSVP | Yes | TownSq, HOA Express |
| Document archive (certificates, clearances, print) | Yes (staff-issued docs, templates, printable) | FrontSteps/Buildium document libraries |
| Architectural review requests (ARC) | No — candidate feature | Buildium, AppFolio |
| Violation management / enforcement workflow | No — candidate feature | Buildium, AppFolio |
| Amenity/reservation booking | No — candidate feature | TownSq, PayHOA |
| Visitor/gate management, parcel logs | No — candidate feature (PH subdivisions often value this) | Gate management add-ons |
| E-voting on HOA matters | No — candidate feature | some TownSq deployments |
| Mobile access | Yes via responsive PWA manifest (no native app) |

Reference points for further study: PayHOA (payhoa.com), TownSq (townsq.com), Buildium
(buildium.com), AppFolio (appfolio.com), FrontSteps (frontsteps.com) — product feature pages
are useful when arguing feature-parity choices for the defense; combine with PH-specific
guidance (DHSUD HOA rules; RA 10173) which these US products do not cover.

---

## 6. How this was verified

- `composer test` — 256 passing after all changes (config cleared first by the script).
- Index migration applied to the live MySQL DB; full rollback smoke on scratch SQLite
  (`migrate → rollback → migrate`) is clean.
- JS: `node --check` on all edited portal scripts; the admin dashboard's inline scripts were
  re-extracted from a live render and syntax-checked as a set.
- The two critical bugs (const reassignment, member IDOR) were reproduced with direct
  evidence (Node execution / HTTP requests) before and after the fix.
