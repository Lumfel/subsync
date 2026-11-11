# SubSync — Revision Audit & Integration Log

Verified against the codebase on 2026-10-03 (branch `test-this`).
This is an evidence-based audit of the 28 requested revisions. Each item is
marked **Done**, **Partial**, or **Missing**, with `file:line` evidence and the
concrete work required to integrate it.

> Status legend: **DONE** = fully present · **PARTIAL** = partially present or
> present but broken/disconnected · **MISSING** = not present.

---

## Status Matrix

| # | Area | Revision | Status |
|---|------|----------|--------|
| 1 | General | Revise objectives/content wording | PARTIAL |
| 2 | General | Formal/professional portal UI | PARTIAL |
| 3 | General | More readable interface | DONE |
| 4 | General | Bigger fonts where needed | DONE |
| 5 | General | Avoid Messenger/social-media look | PARTIAL |
| 6 | General | Landing page introduces SubSync | DONE |
| 7 | Admin | Automatic date/time on announcements | PARTIAL (bug) |
| 8 | Admin | Standardized templates (announce/issues/reports) | PARTIAL |
| 9 | Admin | File archiving for old records/files | PARTIAL (bug) |
| 10 | Admin | Search bar | DONE |
| 11 | Admin | Date picker for reports | DONE |
| 12 | Admin | Reports with pictures + attachments | PARTIAL |
| 13 | Admin | Admin sets report priority | PARTIAL |
| 14 | Admin | Terra Nova HOA Policies section | PARTIAL |
| 15 | Resident | Formal/clean/readable resident UI | DONE |
| 16 | Resident | Organized report submission | DONE |
| 17 | Resident | Photos + supporting files on reports | PARTIAL |
| 18 | Map | Replace heat map with numbered markers | PARTIAL |
| 19 | Map | Numbered marker per issue | PARTIAL |
| 20 | Map | Geofencing within subdivision | DONE |
| 21 | Map | Define map limitations | PARTIAL |
| 22 | Map | Subdivision focus, not advanced GIS | DONE |
| 23 | Security | Address resident conflicts over reports | PARTIAL |
| 24 | Security | Reports documented & organized | PARTIAL |
| 25 | Security | Residents can't modify others' reports | DONE |
| 26 | Security | Admin reviews/manages issues | DONE |
| 27 | Unique | Highlight SubSync uniqueness | PARTIAL (doc) |

**Result:** 9 DONE · 18 PARTIAL · 1 MISSING-equivalent (none fully absent).

---

## Progress Log

### 2026-10-03 — GCash receiving QR (admin-managed)
Admin can change the GCash receiving account + QR code (nav panel, live resident preview,
upload/replace/remove). New: `gcash_settings` table, `GcashSetting` model,
`GcashSettingController`, `GET/POST /api/gcash-settings`. Tests: `GcashSettingTest` (4).

### 2026-10-03 — Phase 1 bug fixes
| Bug | Fix |
|---|---|
| Announcement time showed 12:00 AM | Controller now sends `M d, Y g:i A` (items 7) |
| Officer-file archive button 404'd | Added `PATCH /api/officer-files/{id}/archive`; group now `admin,officer` (items 9) |
| Admin heatmap toggle `#layer-heatmap` TypeError | Null-guarded the listener (items 18) |
| Admin issue pins unnumbered | Now use `makeNumberedIcon(color, idx+1)` + `issueMarkerCount` (items 19) |
| Auto-priority always "Low" | `autoSetPriority()` keys aligned to resident categories (items 13) |
| Images rejected by file upload | `OfficerFileController` mimes now include png/jpg/jpeg/gif/webp (items 12) |

Tests: `Phase1FixesTest` (4) + `IssuePriorityTest` (2). Full suite: **13 passing**.

### 2026-10-03 — Phases 2–11 completed

| Phase | Delivered |
|---|---|
| 2 Map revision | Removed all heat layers + `leaflet-heat`; numbered issue pins (admin now uses `makeNumberedIcon`); map limitation notice added to resident map + `/mapping`. |
| 3 File standards | Resident supporting-documents upload; DB-backed announcement templates (`GET/POST/PUT/DELETE /api/announcement-templates`, seeded); report-template routes registered; issue archiving (`archived_at` + `PATCH /api/issues/{id}/archive` + archive view). |
| 4 HOA Policies | Admin CRUD (`POST/PUT/DELETE /api/hoa-policies`) + management UI; all roles can read. |
| 5 Formalization & UI | Formal messaging CSS (no bubbles/Messenger feel), presence dot + "Online" removed; Inter loaded consistently; dead views deleted (`main`, `welcome`, `analytics`, `reports`). |
| 6 Security | `IssueAuditLog` + audit trail on status/priority/archive changes; `GET /api/issues/{id}/history`; explicit staff-authorization guard. |
| 7 Content | Landing copy revised; Objectives + Uniqueness documented (this file). |
| 8 Google login | `laravel/socialite`; `google_id`/`avatar` + nullable password on residents/officers; `GoogleAuthController` + `/auth/google` routes; "Sign in with Google" button; env placeholders. |
| 9 Officer urgency + map | Priority editor in the officer respond modal; "Urgent first" sort; on-demand Leaflet "View on map" pin per issue. |
| 10 Common issues | `GET /api/issues/common` (ranked by frequency) + admin & officer ranked panels. |
| 11 GCash payments | `payment_submissions` + `PaymentController`/`PaymentSubmission`; resident submit-with-receipt, admin validation queue (approve/reject/needs-info, official receipt), private receipt streaming, transparency summary; resident Payments + Transparency UI. |

**Test suite:** `php artisan test` → **35 passing (120 assertions)** across `Phase1FixesTest`, `Phase3Test`, `Phase4Test`, `Phase6Test`, `Phase8Test`, `Phase9Test`, `Phase10Test`, `Phase11Test`, `GcashSettingTest`, `IssuePriorityTest`.

**Still requires user input:** Google Cloud OAuth credentials (`GOOGLE_CLIENT_ID/SECRET/REDIRECT_URI`) — the Google button won't complete until set. GCash is a manual transfer + receipt-validation flow (no merchant API needed). OCR extraction of receipts was intentionally not built (manual entry baseline).

---

## Detailed Findings

### General / UI

**1. Revise objectives/content wording — PARTIAL (documentation task)**
- Purpose copy exists on the landing page: `resources/views/landing.blade.php:6,95,97,110,123`.
- No formal objectives/mission/"about" block anywhere; `README.md` is the default Laravel readme.
- **Integration:** update the capstone objectives text (the wording revision you pasted), and optionally add a short "About SubSync" panel. No code required beyond landing copy polish.

**2. Formal/professional portal UI — PARTIAL**
- Portal shell (sidebar + topbar + cards) is formal: `admin-dashboard.blade.php:32-55,72-105`; `officer-portal.blade.php:30-52`.
- The **messaging module is still Messenger-style** (chat bubbles, circular avatars, presence "Online" dot): `public/css/ui-polish.css:346-391`; `residents.blade.php:278-334,701`; `admin-dashboard.blade.php:573-596`.
- **Integration:** restyle messaging into a formal, transcript/log-style view (remove bubbles/avatars/presence, use a structured message log with From/Subject/Timestamp).

**3. More readable interface — DONE**
- Global scale-up: `public/css/ui-polish.css:106` (body 18px/1.65), headings `:108-111`, inputs `:120`, tables `:137-138`.

**4. Bigger fonts where needed — DONE (minor gaps)**
- Large sizes in use across portal + landing (`ui-polish.css:106-191`, `landing.blade.php:36,51`).
- Remaining small text: portal meta/labels and chat previews at 10–13px (`ui-polish.css:153,158-160`; `admin-dashboard.blade.php:200,214,363,377`).
- **Integration:** bump these residual small labels if the panel wants uniform sizing.

**5. Avoid Messenger/social-media look — PARTIAL**
- No story/feed/reaction UI exists (good), but the chat module reproduces a Messenger aesthetic: bubbles with asymmetric radius, circular avatars, color-coded avatar palette, "Online" presence dot (`ui-polish.css:346-391`, `:373-374`; `residents.blade.php:306,701`).
- **Integration:** same work as item 2 — redesign messaging to a formal correspondence style.

**6. Landing page introduces SubSync — DONE (cleanup needed)**
- Wired landing: `routes/web.php:21-24` → `resources/views/landing.blade.php` (hero, capabilities, trust band, footer).
- **Dead files:** `resources/views/layouts/main.blade.php` (orphaned legacy shell) and `resources/views/welcome.blade.php` (default Laravel) are unreferenced.
- **Integration:** delete the two dead views; optionally enrich the landing intro.

### Admin Side

**7. Automatic date/time on announcements — PARTIAL (bug)**
- `timestamps()` exists (`migrations/2026_05_17_000006_create_announcements_table.php:16`); `event_date` is a separate optional field.
- **Bug:** `AnnouncementController.php:44,91` format as `'M d, Y'`, stripping the time; admin JS re-parses a date-only string so time always shows **12:00 AM** (`admin-dashboard.blade.php:2551`).
- **Integration:** return full datetime (ISO-8601 or `'M d, Y g:i A'`) from the controller; render time-of-day in admin + resident views.

**8. Standardized templates (announcements, issues, reports) — PARTIAL**
- `AnnouncementTemplate` model+migration exist but are **never used**; controller imports it only (`AnnouncementController.php:7`).
- Announcement presets are hardcoded client-side (`admin-dashboard.blade.php:1620-1626,2646-2658`).
- `ReportTemplate` + `ReportController` exist but are **completely unrouted** (only `use ...ReportController;` at `routes/web.php:15`).
- **Integration:** wire `AnnouncementTemplate` CRUD + selection into the announcement form; register routes for report templates & generation; define a consistent field structure for issues.

**9. File archiving for old records/files — PARTIAL (bug)**
- Announcement archive works (`routes/web.php:165`; `AnnouncementController.php:103-110`).
- Officer files have an `archived` boolean + UI (`migrations/2026_09_15_000001_add_archived_to_officer_files_table.php:12`; `OfficerFileController.php:57-63`; `admin-dashboard.blade.php:3186`).
- **Bug:** UI PATCHes `/api/officer-files/{id}/archive` but **no such route exists** → 404 (`routes/web.php:219-223`).
- No `archived_at`; no archiving for issues/reports (only `resolved_at` purge).
- **Integration:** add the archive route; add an archive view/filter; extend archiving to issues/reports with `archived_at`.

**10. Search bar — DONE**
- Global Ctrl/Cmd+K search modal (`public/js/ui.js:33-112,120`), searches residents/households/issues/announcements in memory (`admin-dashboard.blade.php:2140`). Per-section inputs at `admin-dashboard.blade.php:1223,1195,2276`.
- **Optional:** add a server-side `/api/search` for records not preloaded.

**11. Date picker for reports — DONE**
- `reportDateFrom`/`reportDateTo` (`admin-dashboard.blade.php:1471-1476`), wired to CSV exports (`:3214-3269`). Issue list also has date filters (`:1256-1258`) with backend support (`IssueController.php:29-35`).
- **Note:** applies to CSV exports and issue filtering, not the `Report` model.

**12. Reports with pictures + attachments — PARTIAL**
- `IssueReport` fully supports images + documents (`IssueController.php:107-156`; `Media` morph `IssueReport.php:71-74`).
- Admin "Reports & Files" upload input advertises `accept=".pdf,...,.png,.jpg,.jpeg"` (`admin-dashboard.blade.php:1486`) but the validator **rejects images** (`OfficerFileController.php:24`).
- `Report` (template-generated) has **no attachment support** (`migrations/2026_09_16_004855_create_reports_table.php:18-20`).
- **Integration:** fix the officer-file mime rule (allow png/jpg/pdf/etc.); decide whether "reports" == issues (then add a document UI, see item 17) or the `Report` model (then add attachment columns + UI).

**13. Admin sets report priority — PARTIAL (bug)**
- Fully working for issues: `PUT /api/issues/{id}/priority` (`routes/web.php:177`; `IssueController.php:191-199`), admin+officer, UI at `admin-dashboard.blade.php:4820-4831`.
- **Bug (priority is effectively always Low):** resident category options ("Road / Pavement", "Drainage / Flooding", …) do **not** match the `autoSetPriority` keys ("Infrastructure", "Sanitation", …), so auto-priority falls through to Low (`IssueReport.php:79-98` vs `residents.blade.php:725-732`).
- `Report` model has no priority column.
- **Integration:** align category keys with the form options; optionally add priority to `Report`.

**14. Terra Nova HOA Policies section — PARTIAL**
- Static Policies panel duplicated in admin (`admin-dashboard.blade.php:983,1504-1609`) and resident (`residents.blade.php:590,849-954`).
- A `HoaPolicy` DB layer + Terra Nova-style seeder + resident API exist but the admin panel is hardcoded and there is **no admin CRUD** (`HoaPolicySeeder.php:17-63`; `routes/web.php:230-232`).
- **Integration:** build admin CRUD for `hoa-policies`; drive both panels from the DB instead of duplicated HTML.

### Resident Side

**15. Formal/clean/readable resident UI — DONE (minor font issue)**
- Formal palette + tab/panel layout (`residents.blade.php:32-54,577-605,610-954`); shared readability layer (`ui-polish.css:106-174`).
- **Minor:** `DM Sans` is referenced but never loaded; base serif is overridden by an unloaded `Inter` (`residents.blade.php:59,86,134,148`; `ui-polish.css:7,12`).
- **Integration:** load the intended webfont(s) or drop the dead references.

**16. Organized report submission — DONE (no stepper)**
- Single structured form "Report a Concern or Complaint" (`residents.blade.php:717-770`); submit handler `submitIssue()` `:1387-1419`.
- **Optional:** add sections/stepper and per-field inline validation.

**17. Photos + supporting files on reports — PARTIAL**
- Photos: UI + backend + gallery all work (`residents.blade.php:751-754,1400-1403`; `IssueController.php:107-108,132-140`).
- Documents: backend accepts them (`IssueController.php:109-110,144-156`) but **no file input exists** in the resident form → unreachable.
- **Integration:** add a "Supporting documents" input (pdf/doc/docx/xls/xlsx) to the issue form and append to `FormData`.

### Map

**18. Replace heat map with numbered markers — PARTIAL**
- Heat maps currently render in three places: admin analytics (`admin-dashboard.blade.php:4106-4127`), admin main map (`:4602-4612,4691-4692`), resident map (`residents.blade.php:1802-1812,1836`). Plugin `public/vendor/leaflet/leaflet-heat.js`.
- **Bugs:** admin wires `#layer-heatmap` that doesn't exist → TypeError (`admin-dashboard.blade.php:4637-4638`); resident heat toggle is unwired (`residents.blade.php:831`).
- **Integration:** remove all heat layers + the plugin script; drop heat toggles/legend; rely on numbered issue pins.

**19. Numbered marker per issue — PARTIAL**
- Resident map numbers pins (`residents.blade.php:1761-1767,1825`, popup "Report #N").
- Admin defines `makeNumberedIcon(...)` (`admin-dashboard.blade.php:4493-4499`) and UI copy claims numbering, but actual pins use the plain dot `makeCircleIcon` at `:4687`; `issueMarkerCount` is unused.
- **Integration:** call the numbered icon for admin issue pins; consider a stable stored report number (currently it is render-order sequence).

**20. Geofencing within subdivision — DONE**
- Rectangular `maxBounds` + viscosity, admin area editor, and **server-side** coordinate validation for facilities/households/issues (`admin-dashboard.blade.php:4514-4523,4586-4592`; `MapController.php:220-227`; `IssueController.php:119-121,250-257`).
- **Note:** the fence is an axis-aligned rectangle, not a polygon; treated as "approximate".

**21. Define map limitations — PARTIAL**
- Admin notice present: *"The geofence boundary is approximate…"* (`admin-dashboard.blade.php:1415,1457-1460`).
- **Missing in resident portal** (`residents.blade.php` map panel/JS) and on the legacy `/mapping` page.
- **Integration:** add the same limitation/disclaimer text to the resident map and `/mapping`.

**22. Subdivision focus, not advanced GIS — DONE**
- OpenStreetMap raster only; no satellite, WMS, drawing, measurement, routing, geocoding, or clustering. Deliberately bounded to the subdivision.

### Security

**23. Address possible conflicts between residents over reports — PARTIAL**
- No resident-to-resident comment/dispute (responses are staff-only: `issue_responses` has no `resident_id`, `routes/web.php:178`), and own-report views are scoped (`IssueController.php:74`).
- However the map feed exposes every active issue's title/category/status/coords to all residents (`MapController.php:75-87,116-132`).
- **Integration:** optionally add duplicate-report detection + a privacy note; keep interaction staff-mediated (already one-way).

**24. Reports documented & organized — PARTIAL**
- Columns for category/status/priority/`resolved_at`; multiple staff responses; admin filters/search/export (`IssueController.php:181,184`; `admin-dashboard.blade.php:1237-1262,2662-2731,3039-3045`).
- No audit trail (no actor/status-history), and resolved reports + media are **hard-deleted after 30 days** (`PurgeResolvedIssues.php:18-40`).
- **Integration:** add an audit/history record and consider archiving (item 9) instead of hard delete.

**25. Residents can't modify others' reports — DONE (add defense-in-depth)**
- Only `GET /api/issues/my` + `POST /api/issues` are resident-accessible; `myIssues` is hard-scoped (`IssueController.php:74`); `store` sets `resident_id` server-side (`:113-123`); all mutations are admin/officer-gated via `portal.role` (`routes/web.php:173-179`; `PortalRole.php:13-26`).
- **Gap:** no Laravel Policy/Gate (defense-in-depth); staff methods use `findOrFail` without per-record check.
- **Integration:** add an `IssueReportPolicy` and authorize staff mutations.

**26. Admin reviews/manages issues — DONE**
- List + filters, status, priority, respond, urgent triage, officer parity (`IssueController.php:20-63,178-248`; `routes/web.php:175-178`; `admin-dashboard.blade.php:1237-1262,4820-4831`).

### Uniqueness

**27. Highlight what makes SubSync unique — PARTIAL (documentation)**
| Unique factor | Verified |
|---|---|
| Subdivision-specific geofencing | Yes (rectangular, server-validated) |
| Numbered issue markers | Resident yes; admin needs wiring (item 19) |
| Priority-based reporting | Yes but broken mapping (item 13) |
| Photo/file attachments | Photos yes; documents UI missing (item 17) |
| Organized report management | Yes (admin side strong) |
| Formal community portal | Yes (messaging still needs formalizing) |
| Integrated communication + issue reporting | Yes (SEE push, alerts) |
- **Integration:** document these in the capstone paper and fix the two gaps so the claims hold during demo.

---

## Discovered Bugs (fix before defense)

1. **Announcement time shown as 12:00 AM** — controller strips time (`AnnouncementController.php:44,91`).
2. **Officer-file archive button 404s** — route missing (`routes/web.php:219-223`).
3. **Admin map heatmap toggle → TypeError** — `#layer-heatmap` element absent (`admin-dashboard.blade.php:4637-4638`).
4. **Resident heatmap toggle non-functional** — no listener (`residents.blade.php:831`).
5. **Admin issue pins unnumbered** — `makeNumberedIcon` defined but unused (`admin-dashboard.blade.php:4493-4499` vs `:4687`).
6. **Auto-priority always Low** — category option/key mismatch (`IssueReport.php:79-98` vs `residents.blade.php:725-732`).
7. **Image uploads rejected in Reports & Files** — mime rule excludes images (`OfficerFileController.php:24`).
8. **Report/ReportTemplate + AnnouncementTemplate unrouted/unused** — dead DB layers.
9. **Dead views** — `main.blade.php`, `welcome.blade.php`.
10. **Webfont mismatch** — `Inter`/`DM Sans` referenced but not loaded (`ui-polish.css:7,12`).

---

## Additional Planned Feature — Google Login

**Current state: MISSING.** No OAuth anywhere. Not installed: `laravel/socialite`;
`config/services.php` has no `google` block; no `google_id`/`oauth` columns; the
login view has no Google button.

**How the existing auth works (constraints the design must respect):**
- Custom **multi-guard session auth**, three separate models/tables:
  `admin` → `App\Models\Admin`, `officer` → `Officer`, `resident` → `Resident`
  (`config/auth.php`; default guard `resident`).
- Login is AJAX JSON: `POST /login` (resident/officer, `AuthController::residentLogin`)
  and `POST /admin-access` (admin). `AuthController.php:60,165`.
- **Approval workflow:** self-registered users get `status = 'Pending'` and cannot
  log in until admin approval (`AuthController.php:73,93,137,151`).
- Session keys set on login: `active_role`, `resident_id`/`officer_id`/`admin_id`,
  plus `Auth::guard(...)->login()` + `session()->regenerate()`.
- Login placeholders already say **"Gmail Address"** (`login_residen_officers.blade.php:425`),
  so Google SSO fits the intended user identity.
- `password` is cast `hashed` (`Resident.php`); must become **nullable** for SSO-only users.

### Recommended design
- **Scope:** Google login for **residents and officers only**. Keep **admin
  password-only** — the admin path (`/admin-access`) is deliberately hidden; never
  expose admin SSO.
- **Prerequisite package:** `composer require laravel/socialite`.
- **DB (migration on `residents` + `officers`):** add `google_id` (nullable, unique),
  `avatar` (nullable), and make `password` nullable.
- **Config:** add a `google` block to `config/services.php`; env keys
  `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`.
- **Controller:** new `GoogleAuthController@redirect` + `@callback` using Socialite.
- **Routes:** `GET /auth/google` (redirect) and `GET /auth/google/callback`
  (callback); pass the intended role (resident default) via the redirect state.
- **Callback logic:**
  1. Resolve by `google_id`, else link by matching `email`.
  2. New user → create with `status = 'Pending'` (matches existing approval rule) and show "awaiting admin approval".
  3. Active user → `Auth::guard(...)->login()`, set session keys, `regenerate()`, redirect to `/residents` or `/officer-portal`.
- **UI:** add a formal "Sign in with Google" button under `#loginBtn`
  (`login_residen_officers.blade.php:437`); keep the design non-social-looking.
- **Google Cloud Console:** create an OAuth 2.0 **Web** client. Register **both**
  redirect URIs, because the app is demoed via ngrok as well as locally:
  `http://localhost/auth/google/callback` and
  `https://preschool-yearling-washout.ngrok-free.dev/auth/google/callback`
  (Google requires an exact match — a change of tunnel/domain needs a new URI).
- **Security notes:** Socialite handles the OAuth `state` (CSRF) automatically.
  Optionally restrict to the `@gmail.com` domain (placeholders imply Gmail users)
  or the HOA domain. Add `google_id` to `$hidden` where relevant.

### Effort
Medium. Prerequisites: Google Cloud OAuth client + a Socialite install. No change
to the admin auth flow.

---

## Additional Planned Features (Round 2)

### A. GCash Payments with Receipt Validation & Transparency

> **Implemented so far (admin-managed receiving QR):** the admin can now change the GCash
> receiving account and QR code. Added `gcash_settings` table + `GcashSetting` model +
> `GcashSettingController` (`GET /api/gcash-settings` for all roles, `POST /api/gcash-settings`
> admin-only with QR image upload/remove) and an admin **"GCash Payments"** panel (nav item +
> `#panel-gcash`, live "Resident Preview"). Storage: `public` disk under `gcash/`. Tests:
> `tests/Feature/GcashSettingTest.php` (4 passing). The rest of this section is still to build.

**Current state:** payment routes were deliberately **removed** (`routes/web.php:147,181`),
but the **scaffolding for GCash already exists and is reusable**:
- `financial_records` already has `gcash_transaction_id` (unique), `gcash_reference`,
  `payment_gateway` (`migrations/2026_05_17_000009_create_financial_records_table.php:18-21`)
  — **but** `App\Models\FinancialRecord` does not list/cast them (`FinancialRecord.php:27-29`) → must add.
- `HoaExpense` (`hoa_expenses`) already stores `amount`, `category`, `status`, `receipt_path`
  and is exposed to residents via `GET /api/hoa-expenses` (`HoaExpenseController.php`) — this is
  the **"where the money goes"** side already built.
- Legacy but unused: `ExpenseRequest`, `PaymentStatus` models; dead admin financial JS
  (`admin-dashboard.blade.php:3089,3669,4034`) pointing at removed routes.

**Important reality:** this is a **manual GCash transfer + proof-of-payment upload + staff
validation** flow. It does **not** need the GCash merchant API (which requires a
business/merchant account). "Screenshot turned into data" = OCR extraction into editable fields.

**Proposed data model — new `payment_submissions` table / `PaymentSubmission` model:**
| Column | Purpose |
|---|---|
| `resident_id`, `amount`, `method='gcash'` | who / how much |
| `reference_no`, `gcash_sender_name`, `gcash_number`, `paid_at` | resident-entered GCash data |
| `receipt_path` | resident's GCash screenshot (**side 1**) |
| `ocr_amount`, `ocr_reference`, `ocr_date`, `ocr_sender` | structured data extracted from the screenshot |
| `status` | `Pending` / `Validated` / `Rejected` / `Needs Info` |
| `validated_by` (admin_id), `validated_at`, `admin_response`, `rejection_reason` | validation + response |
| `official_receipt_path`, `acknowledged_at` | admin/treasurer acknowledgement (**side 2**) |
| `financial_record_id` | link to the ledger entry created on approval |

**Workflow:**
1. Resident → Payments panel → submit amount, reference no., sender name, date, **upload screenshot**.
   (Optional OCR pre-fills the fields; resident can correct before submitting.)
2. Status `Pending` → admin validation queue shows the image + extracted data side by side.
3. Admin **Approve** → system creates a `FinancialRecord` (`record_type=Payment`,
   `payment_gateway=gcash`, `gcash_reference`, amount, resident) so it enters the ledger, and
   attaches the **official receipt**. **Reject / Needs Info** → reason + response to resident.
4. Resident sees the response and **both receipts** (their screenshot + the official one) →
   "receipt from both sides for authenticity".

**Transparency (where the money goes):**
- Extend the resident Expenses panel into a **Transparency** view: **Total collected**
  (sum of validated payments) vs **Total spent** (sum of `HoaExpense`) vs balance, plus the
  expense list with each `receipt_path`. Add a funding category breakdown.

**Endpoints:** `POST /api/payments` (resident, multipart), `GET /api/payments/my` (resident),
`GET /api/payments` (admin/officer queue), `POST /api/payments/{id}/validate` (admin),
`GET /api/transparency/summary` (resident) — plus reuse `GET /api/hoa-expenses`.

**UI:** resident Payments + Transparency panels; admin "Payments" panel (validation queue,
receipt viewer, approve/reject with response); officer read-only view (optional).

**Security:** residents only see their own submissions; image mime/size validation; admin-only
validation; store receipts on a **private disk** and serve via an authorized route (receipts are
sensitive). Fix the `OfficerFileController` image-mime bug (audit item 12) since the same storage
path is used.

**Effort: High.** Optional OCR adds effort; plan manual entry as the baseline.

---

### B. Officer Urgency Control & Issue Map Pin

**Current state:**
- `PUT /api/issues/{id}/priority` is already permitted for officers
  (`routes/web.php:173-178`, `portal.role:admin,officer`) and officers drag status + respond
  (`officer-portal.blade.php:1218-1233`), **but the officer UI has no urgency/priority editor**.
- The **officer portal has no map at all** (map audit). Issues store `latitude`/`longitude`,
  and `GET /api/map/issues` is already accessible to officers (`routes/web.php:102`).
- Officer list already shows a priority badge (`officer-portal.blade.php:1190`).

**Proposed:**
1. **Urgency edit** — add a priority/urgency selector (Low/Medium/High/Critical) to the officer
   Respond modal (`#modal-officerRespond`) and persist via the existing
   `PUT /api/issues/{id}/priority` (no backend change). Fold it into `submitOfficerResponse()`.
2. **See urgency** — show/sort urgency in the officer issue list (badge exists; add a
   "Urgent first" sort/filter).
3. **Map pin of the issue** — add a Leaflet map panel to the officer portal using
   `GET /api/map/issues` (numbered pins, matching the map revision), plus a per-issue
   "view on map" action that centers the pin and opens its popup → respond.
4. **Officer edit scope** — allow officers to edit **status + urgency + response**; keep the
   resident's original text/photo immutable (admin-only, for the audit trail — see items 24/25).

**Endpoints:** reuse `PUT /api/issues/{id}/priority`, `GET /api/issues`, `GET /api/map/issues`.
No new backend required except optionally a single-issue detail endpoint.

**Effort: Medium.**

---

### C. Common Issues Ranking (by frequency)

**Current state:** issues carry a `category` string; admin has charts + filters, officer has a
list, but there is **no aggregation/ranking endpoint**.

**Proposed:**
- New endpoint `GET /api/issues/common` (admin/officer; resident optional) →
  `SELECT category, COUNT(*) total, SUM(status='Resolved') resolved, SUM(status!='Resolved') open
  GROUP BY category ORDER BY total DESC`, with optional date-range filter.
- UI: **Admin** "Most Reported Issues" ranked bar/list in analytics; **Officer** "Common Issues"
  ranked panel; optional resident view of top community concerns.
- **Dependency:** fix the category-option vs `autoSetPriority` key mismatch (audit item 13 first)
  so categories and ranking are consistent.

**Effort: Low.**

**Cross-feature note:** all three reuse existing infrastructure — `financial_records` GCash
columns, `hoa_expenses` receipts, the map API, and the existing issue priority endpoint — so
this is mostly wiring plus one new payments table, one aggregation endpoint, and UI panels.

---

## Prepared Integration Plan

Ordered by dependency and demo impact. Each phase is independently shippable.

### Phase 1 — Quick bug fixes (low effort, high credibility)
- Announcement full date/time (item 7).
- Add officer-file archive route (item 9).
- Fix admin heatmap toggle TypeError; remove resident dead toggle (items 18).
- Wire admin numbered issue pins (item 19).
- Align issue category keys with the resident form so auto-priority works (item 13).
- Allow images in officer-file uploads (item 12).

### Phase 2 — Map revision (items 18–22)
- Remove all heat layers + `leaflet-heat.js`; remove heat toggles/legend.
- Confirm numbered pins on admin + resident; add a stable report number.
- Add map limitation notice to resident map + `/mapping`.
- Keep rectangular geofence; document it as approximate.

### Phase 3 — Notifications & file standards (items 8, 9, 12, 17)
- Add "Supporting documents" input to resident issue form (item 17).
- Wire `AnnouncementTemplate` CRUD + selection (item 8).
- Extend archiving to issues/reports with `archived_at` + archive views (items 9, 24).
- Register `ReportTemplate`/`ReportController` routes if report templates are to be used (items 8, 11, 13).

### Phase 4 — HOA Policies CRUD (item 14)
- Admin CRUD for `hoa-policies`; drive admin + resident panels from the DB.

### Phase 5 — Formalization & UI (items 2, 5, 15)
- Redesign messaging from chat bubbles → formal correspondence/log view across admin, officer, resident.
- Load the intended webfonts; remove dead references.
- Remove dead views (`main.blade.php`, `welcome.blade.php`).

### Phase 6 — Security hardening (items 23–26)
- Add `IssueReportPolicy` + authorize staff mutations (defense-in-depth).
- Add status/priority audit trail; switch 30-day hard-delete to archive where required.
- Optional: duplicate-report detection; privacy scoping on the map issue feed.

### Phase 7 — Content & documentation (items 1, 27)
- Revise objectives/content wording in the paper and landing copy.
- Document the uniqueness factors and the map's stated limitations.

### Phase 8 — Google login (new feature)
- `composer require laravel/socialite`; add `google` config + env keys.
- Migration: `google_id` + `avatar` + nullable `password` on residents/officers.
- `GoogleAuthController` + `/auth/google` routes; role-aware callback.
- "Sign in with Google" button on the resident/officer login page.
- Register localhost + ngrok redirect URIs in Google Cloud Console.
- Feature test the callback (link existing, create-pending, active login).

### Phase 9 — Officer urgency + issue map pin (feature B)
- Add urgency selector to the officer Respond modal (reuse `PUT /api/issues/{id}/priority`).
- Add "Urgent first" sort/filter to the officer issue list.
- Add a Leaflet map panel to the officer portal (`GET /api/map/issues`, numbered pins) +
  per-issue "view on map".

### Phase 10 — Common issues ranking (feature C)
- Fix category-option vs `autoSetPriority` mismatch first (audit item 13).
- `GET /api/issues/common` aggregation endpoint.
- "Most Reported Issues" panel on admin + officer.

### Phase 11 — GCash payments + transparency (feature A)
- Migration `payment_submissions`; add GCash fields to `FinancialRecord` fillable.
- `PaymentSubmission` model + `PaymentController` (submit / my / queue / validate).
- Resident Payments panel (upload screenshot) + Transparency summary (collected vs spent).
- Admin validation queue (image + extracted data, approve/reject + response, official receipt).
- Both-sides receipts visible to resident; private receipt storage + authorized serving.
- Optional: OCR extraction (Tesseract.js or an OCR API) with manual fallback.

---

## Key Files

| Concern | File |
|---|---|
| Landing | `resources/views/landing.blade.php` (live), `layouts/main.blade.php` + `welcome.blade.php` (dead) |
| Admin portal | `resources/views/layouts/admin-dashboard.blade.php` |
| Officer portal | `resources/views/layouts/officer-portal.blade.php` |
| Resident portal | `resources/views/layouts/residents.blade.php` |
| Shared UI | `public/css/ui-polish.css`, `public/js/ui.js`, `public/vendor/leaflet/leaflet-heat.js` |
| Issues | `app/Http/Controllers/IssueController.php`, `app/Models/IssueReport.php`, `app/Models/IssueResponse.php` |
| Announcements | `app/Http/Controllers/AnnouncementController.php`, `app/Models/Announcement.php`, `app/Models/AnnouncementTemplate.php` |
| Reports/templates (dead) | `app/Http/Controllers/ReportController.php`, `app/Models/Report.php`, `app/Models/ReportTemplate.php` |
| Files/archiving | `app/Http/Controllers/OfficerFileController.php`, `app/Models/OfficerFile.php` |
| Policies | `app/Models/HoaPolicy.php`, `app/Http/Controllers/HoaPolicyController.php`, `database/seeders/HoaPolicySeeder.php` |
| Map | `app/Http/Controllers/MapController.php`, `app/Models/MapSetting.php`, `resources/views/layouts/mapping.blade.php` |
| Routes | `routes/web.php` |
| Authorization | `app/Http/Middleware/PortalRole.php`, `bootstrap/app.php` |
| Auth (current) | `app/Http/Controllers/AuthController.php`, `config/auth.php` |
| Google login (to add) | new `app/Http/Controllers/GoogleAuthController.php`, `config/services.php`, `resources/views/layouts/login_residen_officers.blade.php` |

---

## System Objectives (Revised)

The study aims to design and develop a web-based community management platform for residential
subdivisions that enhances resident engagement through communication, issue reporting,
recommendations, and interactive mapping. Specifically, it aims to:

1. Design and develop a community management system featuring:
   1.1 An integrated communication module — direct resident↔administrator messaging, official
       announcements, updates, and subdivision events (with automatic date/time).
   1.2 An issue reporting system — residents submit complaints/concerns; administrators monitor,
       prioritize, respond to, and archive reports.
   1.3 A recommendation feature — residents suggest improvements and initiatives.
   1.4 A dynamic mapping feature — facilities, emergency/critical-issue alerts, and reported issues
       shown geographically within a subdivision-specific geofence, with numbered pins and explicit
       limitations (subdivision-only, approximate); no advanced GIS.
2. Test the functionality of the features above (see `tests/Feature`).
3. Evaluate the system using the ISO/IEC 25010 evaluation tool: Functional Suitability,
   Performance Efficiency, Usability, Compatibility, Reliability, Security, Maintainability,
   Portability.

> Note: "emergency tracking" is implemented as **emergency/critical-issue alerting**, not live GPS tracking.

### What Makes SubSync Unique
- Subdivision-specific geofencing (server-validated boundaries; rectangular, approximate).
- Numbered issue markers on the map (report sequence).
- Priority-based reporting with automatic priority from category plus staff override.
- Photo and document attachments on reports.
- Organized report management: statuses, responses, archiving, and an audit trail.
- Formal community portal (no social-media/Messenger styling).
- Integrated communication and issue reporting (announcements + alerts + messaging linked to reports).
- Admin-managed GCash receiving account/QR for transparent payments.

---

## Finalization Pass

- **Google login**: new accounts require admin approval (per decision); login page now displays the
  flash message (was silent); resident Google **avatar** shown in the portal; new residents without a
  household are routed to a **Block & Lot** completion step (`/auth/google/complete`).
- **Consistent confirmations**: every destructive action across admin/officer/resident now uses the
  styled `SubUI.confirm` panel (member/conversation/registration/record/QR/policy/facility/announcement/file).
- **Profile photo persistence**: residents can upload a profile picture; `POST /api/residents/avatar`
  stores it and updates `residents.avatar` (Google photo and uploaded photo both supported).
- **Polished buttons**: shared `.btn` system + variants (`primary`/`ghost`/`danger`/`green`) + confirm panel
  styling in `ui-polish.css`.
- **HOA fund transparency**: admins record expenses (`POST/PUT/DELETE /api/hoa-expenses`) with receipts;
  residents see collected/spent/balance + a **category breakdown** and expense receipts.
- **Known dead code (left in place)**: `layouts/manage_users.blade.php` and its legacy controllers
  (`HouseholdController`/`MemberController`/`StatusController`/`UserController`) redirect to a
  non-existent `/manage_users` route; superseded by the inline Manage Users panel.

**Test suite:** `php artisan test` → **46 passing (163 assertions)**.
