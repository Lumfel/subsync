# SubSync — End-to-End Flow Audit

Reviewed the major user flows on 2026-10-03. This logs where the flow is sound and where it
**doesn't make sense** (dead ends, contradictions, disconnected data).

## Fixed in the first pass (2026-10-03)
- **#1 + #2 — Financial model connected.** Validating a GCash payment now **reduces the resident's
  `current_balance`** (and records the `FinancialRecord`), and the balance is **shown in the resident
  portal** (Overview "Outstanding Balance" card, fed by `/api/resident/portal-data`). Assumption:
  *per-resident dues + payments* modeled alongside community transparency (`max(0, balance − payment)`).
- **#3 — Officer approval.** Admins can now **Approve** a `Pending` officer (button in the Officers
  table), the status dropdown includes `Pending` and no longer mislabels it as `Inactive`, and the
  `PUT /api/officers/{id}` validation accepts `Pending`.
- **#7 — Purge → archive.** `issues:purge-resolved` now **archives** resolved issues older than 30 days
  (`archived_at`) instead of hard-deleting them (keeps responses/media/audit trail).
- **#4 + #10 — Symmetric onboarding + household dedupe.** The manual resident registration form already
  had Block/Lot fields, but they weren't sent — now wired through, and `AuthController::register`
  links/creates the household. Added `Household::findOrCreateByBlockLot()` (case- and
  whitespace-insensitive) used by both manual registration and the Google completion flow, preventing
  duplicate households.
- **#9 (partial) — Dead-code cleanup.** Removed orphaned views (`finance`, `manage_users`, `members`,
  `payment_status`, `login_1`) and unrouted controllers (`MemberController`, `HouseholdController`,
  `StatusController`, `UserController`). *(The dead finance JS in `admin-dashboard.blade.php` that
  references `/api/financial*` remains — unreachable, but still interleaved with active functions.)*

Tests: `tests/Feature/FlowFixesTest.php` (5). Full suite: **53 passing**.

## Still open
- **#5 report templates** — seeded (`ReportTemplateSeeder`: Incident Report, General Purpose Report) and
  routed, but **no UI** exists. Needs a decision: **build the UI** (admin template CRUD + resident
  generate/view) or **remove the feature**.
- **#6 "Reports" naming** — resolve alongside #5.
- **#9b dead finance JS** in `admin-dashboard.blade.php` (references `/api/financial*`; unreachable but
  interleaved with active functions — deferred as risky to remove without browser verification).
- **#11 officer announcement archive** — minor; archive is admin-only by design since the officer modal
  buttons were removed.

`#8 /mapping` is **not** an issue — it's an intended public page covered by a test (`ExampleTest`); its
dead sidebar links were fixed in this pass.

---

## Flows that make sense ✓
- **Auth**: resident/officer self-register → `Pending` → admin approves → login. Admin stays
  password-only; Google login → new accounts `Pending` → admin approves.
- **Announcements**: admin/officer post (auto date/time) → residents see active → archive hides.
- **Issues**: resident submits (category/photos/docs, geofenced location) → auto-priority from
  category → staff update status/priority + respond → resolve → audit trail + archive + common-issues.
- **Recommendations**: resident submits → staff update status.
- **Messaging**: resident ↔ admin/officer threads.
- **Compliance/Maps/Files/Policies**: admin configures; residents view.

---

## Flow issues (don't make sense)

| # | Severity | Issue | Why it's a problem | Recommended fix |
|---|----------|-------|--------------------|-----------------|
| 1 | **High** | **Two disconnected money ledgers.** `payment_submissions` (GCash, validated) feeds transparency "Collected"; `financial_records` is created on payment validation but never read; `residents.current_balance` is never updated by payments. | A validated payment increases "Collected" but never reduces the resident's balance. The ledger that residents would care about (what *I* owe) is disconnected from the ledger the system shows (community transparency). | Decide the model: **(a)** drop per-resident `current_balance`/`financial_records` entirely (pure community transparency), or **(b)** make a validated payment create/decrement a balance entry and surface it. |
| 2 | **High** | **Resident balance/dues is half-removed.** `.res-balance-card` CSS exists but no HTML uses it; `portal-data` doesn't return `current_balance`; the overview copy still says "check account balances". | Misleading copy + orphaned UI; residents can't see any balance. | Either implement the balance card (with the chosen model in #1) or remove the CSS/copy. |
| 3 | **High** | **Officer self-registration has no clear approval path.** Officers register `Pending`, but the admin "Pending Registrations" badge/section covers **residents only**. The Officers edit dropdown offers only `Active/Inactive` and defaults a `Pending` officer to **"Inactive"**. | A self-registered officer stays `Pending` (login blocked) with no prompt; the admin's only route is to edit status to `Active` (the UI even mislabels Pending as Inactive). Officers can be permanently locked out. | Add `Pending` handling: include officers in the pending badge/notice, add an explicit **Approve** action, and stop mislabeling `Pending` as `Inactive`. |
| 4 | **Medium** | **Asymmetric onboarding.** Google residents complete **Block/Lot** (auto-creates a household); manual registrations collect **no** block/lot (`house_id` null). | Same account type, different data completeness; manually-registered residents show a blank Block/Lot forever. | Collect block/lot on the manual registration form too (or prompt post-login for any resident without one). |
| 5 | **Medium** | **Report templates are a stranded feature.** Routes `/api/report-templates`, `/api/reports/generate/{id}`, `/api/reports/my` exist, but **no view** calls them. No template CRUD UI, no generate UI, no "my reports" UI. | A whole feature (DB + controller + routes) with no user access — pure dead weight/confusion. | Either build the UI (admin template manager + resident generate/view) or remove the routes/controller/model. |
| 6 | **Medium** | **Naming overload: "Reports".** Admin "Reports" panel = CSV exports + file uploads; "Issue Reports" = resident issues; "Report templates"/`Report` model = stranded (#5). | Three different meanings of "report" confuse users and maintainers. | Rename for clarity (e.g. "Exports & Files", "Community Issues") and resolve #5. |
| 7 | **Medium** | **30-day hard-delete contradicts archiving/documentation.** Resolved issues are **hard-deleted** after 30 days (`PurgeResolvedIssues`) even though archiving exists and the objectives call for "properly documented and organized records". | Loses history/audit trail for resolved issues; conflicts with the transparency/organization goals. | Change the purge to **archive** (`archived_at`) instead of delete, or make retention configurable. |
| 8 | **Low** | **`/mapping` standalone page is orphaned.** Routed publicly but nothing links to it; duplicates the in-portal maps. | Dead surface area; its sidebar was full of dead links (fixed) and no entry point. | Keep as a deliberate public page with a link, or remove the route/view. |
| 9 | **Low** | **Legacy dead code referencing removed features.** Admin finance functions call `/api/financial*` (no routes); orphan views (`finance`, `manage_users`, `members`, `payment_status`, `login_1`) and unrouted controllers (`MemberController`, `HouseholdController`, `StatusController`, `UserController`). | Not user-reachable, but muddies the flow and hides real dead ends. | Delete the dead functions/views/controllers in a cleanup pass. |
| 10 | **Low** | **Household auto-creation from free-text Block/Lot** (Google completion) has no uniqueness guard (`firstOrCreate` by exact string). | "Block 3 Lot 12" vs "block 3 lot 12" can create duplicate households. | Normalize/validate block-lot input; enforce uniqueness. |
| 11 | **Low** | **Officer archive/delete of a viewed announcement was removed** (buttons were non-functional); officers can only delete their own via "My Announcements", not archive. | Inconsistent capability vs. the `admin,officer` archive route. | Decide: either give officers a working archive action, or keep it admin-only and hide accordingly. |

---

## The core conceptual tension
SubSync mixes **two financial models**:
- **Community transparency** — "Collected vs Spent" for the whole HOA (current direction).
- **Per-resident billing** — `current_balance` / dues / `financial_records` (legacy scaffolding).

The app committed to community transparency (GCash + HOA expenses) but left the per-resident
billing scaffolding in place. Issues **#1, #2, #7** all stem from this. **Resolving the model
(#1) is the key decision** that unblocks the others.

## Next actions (remaining, priority order)
1. Resolve report templates (#5/#6) — build the UI, or remove the stranded routes/models.
2. Symmetric onboarding for block/lot (#4) + normalize/dedupe household input (#10).
3. Cleanup dead code (#8 `/mapping`, #9 orphan views/controllers) and the officer announcement
   capability (#11).
