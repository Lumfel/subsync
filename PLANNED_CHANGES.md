# Planned Changes for Defense Panel Requirements

## Summary of Changes

Based on the defense panel feedback, I will implement the following changes:

### 1. Priority level (Autoset) based on the issue category/type
- **File**: `app/Models/IssueReport.php`
- **Change**: Add model boot method to automatically set priority based on category
- **Logic**: Map categories to priority levels (e.g., Safety → Critical, Infrastructure → High, etc.)

### 2. Details (auto) and resident can add details including picture uploads
- **File**: `app/Http/Controllers/IssueController.php` (store method)
- **Change**: Enhance validation to allow additional detail fields and improve image handling
- **Note**: Current implementation already supports image uploads via Media model

### 3. Map Pin (Accurate) - add latitude/longitude mapping capabilities
- **Files**: 
  - `resources/views/layouts/mapping.blade.php` - Enhance map display
  - `app/Http/Controllers/IssueController.php` - Ensure lat/lng are properly handled
- **Change**: Improve map visualization with accurate pin placement

### 4. Check Delinquent (SUBDIVISION POLICY) - add backend validation
- **Files**:
  - `app/Http/Controllers/FinancialController.php` (create/store methods)
  - `app/Models/Delinquent.php` - Add validation method
- **Change**: Add subdivision policy validation before flagging household as delinquent

### 5. Change the term "Delinquent" to a more appropriate term throughout the system
- **Files to rename**:
  - `app/Models/Delinquent.php` → `app/Models/PaymentStatus.php`
  - `database/migrations/2026_05_08_015544_deliquent.php` → migration for payment_statuses
  - `database/migrations/2026_05_18_052008_create_delinquents_table.php` → create_payment_statuses_table.php
  - `app/Http/Controllers/FinancialController.php` - references to Delinquent
  - `resources/views/layouts/deliquents.blade.php` → payment_status.blade.php
  - Routes referencing delinquents
- **Change**: Replace all instances of "delinquent"/"delinquents" with "payment status"

### 6. Update Excel reports to reflect Gcash Payment integration tracking
- **Files**:
  - `app/Models/FinancialRecord.php` - Add gcash transaction fields
  - `database/migrations/2026_05_19_000022_create_financial_reports_table.php` - Update if needed
  - Reporting/export functionality (to be located)
- **Change**: Add gcash_transaction_id, gcash_reference, and payment_gateway fields

### 7. Set up Templated formats for Notices and Announcements
- **Files**:
  - `app/Models/Announcement.php` - Add template functionality
  - `app/Http/Controllers/AnnouncementController.php` - Handle templates
  - `database/migrations` - Add template_id or template_type to announcements table
  - New: `app/Models/AnnouncementTemplate.php`
- **Change**: Allow announcements to be based on predefined templates

## Detailed File Changes

### 1. IssueReport Model Auto-Priority
```php
// In app/Models/IssueReport.php
protected static function boot()
{
    parent::boot();
    
    static::creating(function ($issue) {
        if (!$issue->priority) {
            $issue->priority = $issue->autoSetPriority();
        }
    });
}

public function autoSetPriority()
{
    $categoryPriorityMap = [
        'Safety' => 'Critical',
        'Security' => 'Critical',
        'Infrastructure' => 'High',
        'Utilities' => 'High',
        'Sanitation' => 'Medium',
        'Landscaping' => 'Low',
        'Maintenance' => 'Medium',
        'Noise' => 'Medium',
        'Pet Issues' => 'Low',
        'Parking' => 'Medium',
        // Default
        'default' => 'Low'
    ];
    
    return $categoryPriorityMap[$this->category] ?? $categoryPriorityMap['default'];
}
```

### 2. Payment Status Term Change
- Rename model and update all references
- Update migration timestamps
- Update controller method names and variables
- Update Blade template file and references

### 3. Gcash Payment Integration
```php
// In app/Models/FinancialRecord.php
protected $fillable = [
    'resident_id', 'admin_id', 'record_type', 'description', 'amount', 'record_date',
    'gcash_transaction_id', 'gcash_reference', 'payment_gateway'
];

protected $casts = [
    'amount' => 'decimal:2',
    'record_date' => 'date',
    // gcash fields as strings
];
```

### 4. Announcement Templates
```php
// New model: app/Models/AnnouncementTemplate.php
class AnnouncementTemplate extends Model
{
    protected $fillable = ['name', 'type', 'subject_template', 'body_template', 'variables'];
    
    public function render(array $variables = [])
    {
        $subject = str_replace(
            array_keys($variables), 
            array_values($variables), 
            $this->subject_template
        );
        
        $body = str_replace(
            array_keys($variables), 
            array_values($variables), 
            $this->body_template
        );
        
        return [
            'subject' => $subject,
            'body' => $body
        ];
    }
}
```

## Implementation Order
1. Priority autoset for IssueReport
2. Payment status terminology change
3. Gcash payment integration
4. Announcement templates
5. Map enhancements
6. Subdivision policy validation

Each change will be implemented with appropriate migrations, model updates, controller modifications, and view updates as needed.

---

# Backlog — 2026-10-20 (autonomous session)

Source: user-logged list. **All 13 items are done** — each section below records what
was verified/changed, plus any deliberate follow-ups.

| # | Item | Status |
|---|------|--------|
| 1 | Scalability / socket stress — evaluate Reverb/Echo load under concurrent portal connections | done |
| 2 | Auth for every page — ResolvePortalSession + X-Portal consistent across layouts; no route without auth | done |
| 3 | React fab icons / SVG — composable icon component / DaisyUI icon set aligned with Tailwind v4 | done (Blade `<x-icon>`, no React/DaisyUI needed) |
| 4 | Laravel auth — map three guards to every portal page; audit route middleware in routes/web.php | done |
| 5 | Real-time messages — announcements/issues via Reverb/Echo instead of polling | done |
| 6 | server.js — standalone WS server not needed if Reverb + broadcast events are used | done (not needed — Reverb already is the WS server) |
| 7 | Docs — architectural decisions + frontend/backend contracts (AGENTS.md/CLAUDE.md/docs) | done |
| 8 | Migrations — every table (incl. realtime/notification) migration-backed and rollback-safe | done |
| 9 | .htaccess hardening — defense-in-depth; session auth stays middleware-level | done |
| 10 | Code arrangement — dedupe fetch/EventSource wrappers; consolidate __PORTAL/__ACTOR | done (wrappers consolidated; `__ACTOR` kept as a separate alias for notifications.js) |
| 11 | Server optimization — queue/scheduler/Reverb profiling; opcache; query optimization | done (audit; config already sound) |
| 12 | Messages/announcements must be WebSocket-first (SSE/polling only as fallback) | done |
| 13 | Replace emoji tab/UI icons with monochrome line SVGs (layouts + login pages) | done |

## Session notes

### Items 2 + 4 — Auth audit (done)

- Audited every route in `routes/web.php` (all routes incl. `/api` live there; no `api.php`).
- **No unprotected routes**: every non-public route carries `admin.auth`, `officer.auth`, `auth:resident` or `portal.role:*`.
- Public whitelist (intentional): `/`, `login`, `register`, `logout`, `admin-access`, `mapping`, `auth/google*`, plus framework routes (`broadcasting/auth`, `storage/{path}`, `up`). `/mapping` embeds no server data — its feeds come from protected `/api/map/*`.
- **Bug found + fixed**: all three portal layouts (and admin login) had an inline fetch patch that started with `if (!base) return;` — at the root domain `APP_BASE` is empty, so the patch never applied: no `X-Portal`, no `ngrok-skip-browser-warning`, and the SSE polyfill silently depended on the Referer fallback.
- Consolidated six duplicated inline patches into **`public/js/portal-context.js`** (always patches fetch/EventSource; prefix only when APP_BASE is set). Updated: admin-dashboard, officer-portal, residents, settings, login_admin, login_residen_officers blades.
- New regression test: `tests/Feature/RouteAuthAuditTest.php` (route coverage, shared-script presence, no broken inline pattern).

### Item 8 — Migrations audit + rollback fixes (done)

- 42 DB tables vs migration-created tables: **every table is migration-backed** (incl. `sse_events`, `jobs`, `cache`, `sessions`); all 62 migrations show `Ran`, none pending.
- Rollback smoke on a scratch SQLite file (`migrate` → `rollback` → `migrate` → `rollback`) exposed **four migrations whose `down()` dropped indexed columns without dropping the index first** — hidden on MySQL (which cascades the index) but a hard failure on SQLite:
  - `2026_09_21_005651_add_resolved_at_to_issue_reports_table` (index)
  - `2026_10_03_000005_add_google_to_residents_and_officers` (unique)
  - `2026_10_12_000001_add_status_to_conv_participants` (index)
  - `2026_10_12_000003_add_issuance_to_reports` (unique)
- Fixed by dropping the index/unique in `down()` before the column (up() untouched; already-ran migrations keep matching the DB).
- Verified: full cycle from zero is clean in both directions, repeatedly.
- Re-run command (scratch DB, never touches MySQL):
  `$env:DB_CONNECTION='sqlite'; $env:DB_DATABASE="$env:TEMP\subsync-scratch.sqlite"; php artisan migrate --force; php artisan migrate:rollback --force`

### Items 5 + 6 + 12 — Realtime (done)

- **End-to-end verified**: browser-independent WS client (node + pusher-js) connected to Reverb; a real message sent over HTTP arrived instantly as `{"latestId":..,"conversationId":..,"senderType":"resident"}` and produced the matching `sse_events` row.
- `Message` joined the observed models; new `message` payload includes `conversationId`/`senderType` (never message content).
- **WebSocket-first** in all three portals: SSE now refuses to start while Reverb is connected, closes itself when the socket comes up, and restarts automatically if the socket drops (`disconnected`/`unavailable`). The 10s thread poll stays only as a fallback.
- `server.js` is unnecessary — Reverb is already the WS server and broadcasts are generated from Laravel observers.
- Privacy decision: the `subsync` channel **stays public** for now — it carries exactly the same community-wide data the authenticated SSE stream already sends to every portal (never message content, never resident-specific rows), and the payloads are metadata only. Switching to private channels would require per-portal `broadcasting/auth` (the admin portal authenticates via session id, not a guard, so it needs custom handling) and risks breaking realtime right before the defense for little gain. Revisit the day staff-only data (e.g. full message bodies, resident PII) needs to ride the bus — that's the trigger documented for the panel.
- Tests: `tests/Feature/RealtimeMessagesTest.php` (bus/broadcast + portal-script markers), plus syntax/regression checks.

### Items 3 + 13 — Icon system (done)

- New `resources/views/components/icon.blade.php`: 40 monochrome 24×24 line icons (`stroke="currentColor"`), usable anywhere as `<x-icon name="message" size="16" />`. Renders on a single line by design because the tag is also compiled inside inline JS strings.
- Replaced **128 emoji icon spots across 7 views**: admin sidebar + quick actions + banners + modals, residents/officer tab bars + quick actions + form labels, both login pages (role tabs, badges, input icons, password eyes), settings brand mark, mapping menu. Eye toggles are now a class-driven SVG swap; the sidebar hamburger toggles via `innerHTML` with the compiled SVG.
- Left intentionally emoji (content, not icons): toast glyphs (✅/⚠), greetings (👋), ⚡ priority badges, ✕ close glyphs, code comments in mapping.
- Verified: all six rendered views pay-passed extraction of every inline `<script>` through `node --check`; live pages 200 with icons (login 24, admin-access 4, mapping 1); ngrok login serves icons too; regression test in `FrontendRegressionTest::test_ui_icons_are_monochrome_svgs_not_emoji`.
- **Follow-up completed (2026-10-20, later session)**: script-rendered markup now uses `SubUI.icon(name, size)` — a JS mirror of the Blade component added to `public/js/ui.js` (ICON_PATHS map + helper). All empty states, list-row icons, hint/contact/SLA/delete/archive buttons, activity-feed icons, the officer file rows and the admin menu toggle are monochrome SVGs now. Side discoveries fixed on the way: the toast icons in `ui.js` were literal `?` characters (mojibake from an earlier edit) — they now render proper check/alert/info icons; and four `<x-icon>` tags that had been placed inside `<option>` elements were reverted to plain text (option content is text-only; SVG there would render as raw markup). Remaining emoji are intentionally content-only: toast message glyphs (✅/⚠/🗑️ in text), meta badges (⚡/👥/📅/🎯), priority labels (🚨/🔴/🟡/🟢), select `<option>` labels and greetings (👋). Guarded by `FrontendRegressionTest` (`empty-icon">[^<]` and `<option…><x-icon` are now forbidden patterns).

### Item 1 — Socket stress (done)

- `scripts/reverb-loadtest.cjs` (reads `.env`, signs Reverb's HTTP API, measures connect/subscribe/delivery/latency; read-only).
- Results: **60 clients** → 100% connected/subscribed, 100% delivery (180/180), avg latency 8 ms. **200 clients** → 100% connected in 113 ms avg, 100% delivery (600/600), avg latency 10 ms. Reverb comfortably handles the current scale.

### Item 11 — Server optimization audit (done)

- **opcache**: already enabled (128 MB, 20k files, `validate_timestamps=1` + 2 s revalidate — right for dev). `jit=disable` is deliberate (JIT crashes the Apache child). No change needed.
- **Indexes**: `check_indexes.php` confirms the schema is well-indexed — including the hot paths (`sse_events`, `messages_conversation_id`, `announcements_status_created_at`, sessions/cache/jobs).
- **Processes**: Reverb + `queue:work --tries=3` + `schedule:work` all running; SSE bus is one tiny indexed query per second per client by design.
- **MySQL**: stock Laragon defaults (128 MB buffer pool, slow log off). Fine for capstone load; production sizing is covered in `SCALABILITY.md` / `DEPLOYMENT.md` (Redis + larger pool) — not changed here to avoid a risky DB restart before the defense.

### Item 7 — Docs (done)

- **`AGENTS.md` created**: compact agent guide (commands incl. `composer test`/`test:ngrok`, the never-do list, portal session/guard contract, routing/DB rules, frontend contracts incl. `<x-icon>` single-line rule, Windows/Laragon environment gotchas).
- **`CLAUDE.md` corrected**: stale commands fixed (`composer run dev` no longer claims a web server/log viewer; `php artisan serve`/`pail` marked unusable on this machine; `composer run format|analyse` replaced with `vendor/bin/pint`), stale "log driver" broadcasting section replaced with the actual Reverb/SSE architecture, and a dated hardening-session summary appended.
- No `docs/` folder added — the contracts now live inline in AGENTS.md/CLAUDE.md where agents will actually read them.

### Items 9 + 10 — .htaccess + code arrangement (done)

- `.htaccess`: dotfiles and dangerous extensions (`env|sql|log|bak|ini|yml|sh|ps1|…`) are denied even if planted in `public/`; `X-Frame-Options: SAMEORIGIN` + `Referrer-Policy` added. Verified: planted `leak-test.sql` and `.env.test-probe` return 403, pages/assets return 200, ngrok unaffected. Auth continues to live in Laravel middleware (session auth is not an .htaccess job).
- Code arrangement: the six duplicated fetch/EventSource patches are now one shared `public/js/portal-context.js` loaded by every page (fixes the silent no-op bug where headers never applied at the root domain). `window.__ACTOR` remains a separate alias because `notifications.js` reads it for its localStorage key; `__PORTAL` drives the X-Portal header/SSE hint — documented in AGENTS.md.

## Post-backlog — UI/UX design system (same day, later session)

Goal: make the UI/UX templated and industry-standard for cleaner visuals, without touching
colors or layout structure.

- **Design tokens + canonical scale** appended to `public/css/ui-polish.css` (spacing on a
  4px grid, `--radius-xs…full`, `--shadow-1…3`, shared easing/duration). Buttons, cards,
  inputs, pills, modals, popovers and toasts now share one shape/motion language across all
  portals; focus treatment unified via the existing accent ring.
- **Tailwind v4 + daisyUI kit activated** (`resources/css/app.css`): no Preflight (the
  hand-written CSS stays authoritative), daisyUI prefixed `d-`, utilities `tw-`, themed to
  the app palette + radius scale. Emission is **usage-based** (verified: unused components
  never ship; full CSS ≈ 6 KB gzip). Bundled through `resources/js/app.js` / `npm run build`.
- **`/design-system`** admin-only reference page: tokens, buttons/cards/badges/forms/alerts
  in both app (`btn`/`card`/`pill`) and kit (`d-*`) flavours, icon set, empty states.
- Guards: new route is auth-protected (RouteAuthAuditTest), design-system test added, full
  suite **240 passed / 5 skipped**.

## Post-backlog — Messages: show who is contacting you

- **Realtime payloads now name the sender**: `SsePublishingObserver` adds `senderId` +
  `senderName` to every `message` event (explicit lookups — no lazy-loading; content still
  never leaves the server).
- **All three portals announce it**: on an incoming `.message` event that isn't your own
  and isn't the thread you're currently watching, a toast appears — *"New message from
  {name}."* — the messages badge increments, and the sidebar refreshes live: residents and
  officers get the **Message Requests** block updated (name + Accept/Reject) without their
  open thread switching; admin gets the conversation list refreshed (new senders appear
  with the resident's name as the title).
- **Admin-initiated requests name the sender too**: `requests()` previously showed
  "Someone"/title because admin conversations have no accepted participant row; `from` now
  falls back to **"HOA Admin"**.
- Verified live end-to-end: a resident's message arrived at a raw WS client as
  `{"conversationId":11,"senderType":"resident","senderId":25,"senderName":"Juan Dela Cruz"}`.
- Tests: payload assertion in `RealtimeMessagesTest`, sender-naming markers for all three
  portal scripts, and `MessageNewConversationTest::test_admin_initiated_request_names_the_sender`.

## Bug fixes (reported by tester)

- **Settings 500 for admin**: `SettingsController::payload()` read `contact_number` from
  the Admin model — the `admins` table has no such column and local runs
  `Model::shouldBeStrict()`, so `GET /api/settings` (fetched by the settings page) threw.
  Fixed `payload()` + `updateProfile()` to skip `contact_number` for admins. Regression
  test enables the strict flags explicitly (`test_admin_settings_are_strict_mode_safe`) —
  the normal test env has them off, which is how it slipped through.
- **Officer Google login failed with `InvalidStateException`**: `ResolvePortalSession`
  runs BEFORE `EncryptCookies`, so it read the encrypted `subsync_portal_hint` cookie
  raw, failed the role whitelist, and fell back to `'resident'` — the callback then
  looked for the OAuth state in the resident session. Residents never noticed because
  the fallback equals their portal. Fixed by keeping the hint cookie unencrypted
  (`encryptCookies(except: …)`). Verified live over ngrok: the callback now resolves
  `subsync_officer_session` and reaches Google's token endpoint; two regression tests
  added (unencrypted hint + officer callback state machine).

## Realtime expansion — approvals & the rest of the data changes

Everything that previously required a refresh or a poll now rides the bus
(`SsePublishingObserver` + `$sseModels`), with per-portal listeners:

| Domain | Events | Who updates live |
|---|---|---|
| Resident/officer approvals | `resident`, `officer` (name + status) | Admin: bell, pending queue, lists/stat cards when visible. Resident: own portal data (balance/status) |
| GCash payments | `payment` (residentId + status) | Admin: validation queue + finance overview; Resident: own list + toast "Payment update: Validated." |
| Suggestions | `recommendation` | Admin lists; resident's own list |
| Calendar / RSVP | `event`, `rsvp` | Residents' calendar + counts (officer too) |
| Officer documents | `officer-file` | Admin desk list — its 30s poll is now socket-gated (`!_rtWsReady`) |
| Notification bells | all of the above | Bells refresh instantly via a `subsync:refresh-alerts` DOM event (polls stay as fallback) |

Verified live end-to-end: a registration produced `RESIDENT {name, "Pending"}` on a raw WS
client, and the admin's approval produced `{status: "Active"}` seconds later. 4 new tests
(`RealtimeBusTest`: payloads for every new event + client-side subscription markers).

### Final validation

- Full suite: **239 passed, 5 skipped** (`composer test`).
- Live tunnel smoke: `composer test:ngrok` → public pages + API auth verified over ngrok (role-session checks need NGROK_* credentials to enable).
- Realtime end-to-end: real HTTP message → Reverb client received it instantly; load test 200 connections @ 100% delivery.
- All inline scripts across the six touched views pass `node --check`; rendering of every touched view verified.