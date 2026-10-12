# SubSync — Competitive Benchmark & Improvement Log

**Date:** 2026-10-12
**Purpose:** Compare SubSync against comparable community / HOA / barangay management systems and log concrete, prioritized improvements.
**Method:** Feature review of published product capabilities (marketing/feature pages), cross-checked against SubSync's implemented modules (this repository) and the capstone's related-literature systems.

## Systems reviewed

| System | Type | Notes |
|---|---|---|
| **PayHOA** | US HOA management | Financials, communications, violations, voting, owner portal, resale docs |
| **HOA Express** | HOA website/community platform | Blast email/SMS, documents, calendars, forms, galleries, directories, surveys |
| **FRONTSTEPS** | Community association suite | Accounting, payments, visitor/security, mobile apps, AI assistant |
| **Capstone RRL systems** | PH barangay / local gov | e.g. Webyu, LOKAL Pilipinas, Barangay Blotter & Clearance, E-SITIO, CBMS (from `SubSync_Capstone_Full.md`) |

## Where SubSync already stands out
- **One integrated platform** — communication, issues, recommendations, mapping, finance, documents, reports in a single system (the capstone's core gap statement).
- **Issue lifecycle depth** — categories, priority + auto-priority, SLA due dates/overdue flags, assignment, responses, audit trail, archiving, bulk actions.
- **Transparency** — HOA expenses, policies, ledger/aging, assessments/budgets visible to residents.
- **Real-time** — SSE + WebSocket updates (works behind ngrok after the fetch-based SSE fix).
- **Message requests** — accept/reject new conversations (anti-spam).
- **PH-relevant payments** — GCash submission + admin validation + receipts/statements.
- **PWA + dark mode + role portals** (admin / officer / resident) with an approval workflow.

## Feature comparison

| Capability | SubSync | PayHOA | HOA Express | FRONTSTEPS |
|---|---|---|---|---|
| Announcements | ✅ in-app + realtime | ✅ | ✅ | ✅ |
| Email / SMS blast + open tracking | ✗ | ✅ | ✅ | ✅ |
| Issue/complaint tracking w/ SLA | ✅ | ◐ | ◐ (forms) | ◐ |
| Recommendations/ideas | ✅ | ◐ (message boards) | ◐ | ◐ |
| Direct + group messaging | ✅ | ✅ | ✅ | ✅ |
| Interactive map (facilities/households/issues) | ✅ | ◐ | ✗ | ✗ |
| Violations / CC&R enforcement | ✗ (household status only) | ✅ | ✗ | ◐ |
| Online payments (cards/bank) | ◐ (GCash manual) | ✅ | ✅ | ✅ |
| Autopay / recurring | ✗ | ✅ | ◐ | ✅ |
| Ledger / dues / budgets | ✅ | ✅ | ◐ | ✅ |
| Documents library (folders/permissions) | ◐ (flat officer files) | ✅ | ✅ | ✅ |
| Calendars / events + RSVP | ✗ (event_date only) | ✅ | ✅ | ✅ |
| Voting / surveys / polls | ✗ (recommendations only) | ✅ | ✅ | ◐ |
| Custom / request forms | ✗ (fixed forms) | ✅ | ✅ | ◐ |
| Community directory (opt-in) | ✗ | ◐ | ✅ | ◐ |
| Photo galleries | ✗ | ✗ | ✅ | ◐ |
| Reports / templates / certificates | ✅ (templates + generate; UI added) | ✅ | ◐ | ✅ (AI) |
| Visitor / gate access | ✗ | ✗ | ✗ | ✅ |
| AI assistant / insights | ✗ | ✗ | ✗ | ✅ |
| Mobile app | ◐ (PWA) | ✅ | ✅ | ✅ |
| Public status/changelog/feedback | ✗ | ◐ | ✅ | ✅ |

Legend: ✅ present · ◐ partial · ✗ absent

## Prioritized improvements

### P1 — Quick wins (days, low risk)
> **Status:** #2, #3, #4 implemented 2026-10-12 (with tests). #1 deferred by request.

1. **Announcement delivery by email** (and optionally SMS). *(Deferred — not started.)*
   - *Why:* every benchmark competitor pushes announcements out of the app; in-app-only means residents miss them.
   - *Scope:* queue a mailable on announcement create; respect `user_settings.notify_announcements` / `email_notifications`; log delivery. `Mail` + a queued job.
   - *Touches:* `AnnouncementController`, new `App\Mail\AnnouncementMail`, observer/job, an email template.
2. **Certificate / clearance issuance from report templates.** ✅ **Done**
   - *Why:* the capstone RRL centers on barangay clearance/blotter issuance; SubSync's `report_templates` already model this — make it a *document*, not just text.
   - *Scope:* reference number per generated report, PDF/print view, "Issued by" + date, and a staff "issue on behalf of resident" action.
   - *Touches:* `ReportController` (new `issue`, `staffIndex`, `printView`), `Report` model (`reference_no`, `issued_by`), `resources/views/reports/print.blade.php`, admin "Issue a Certificate / Report" card, resident print links.
3. **Community calendar + RSVP.** ✅ **Done**
   - *Why:* announcements carry `event_date` today; a calendar is table-stakes.
   - *Scope:* `events` + `event_rsvps` tables, staff CRUD, resident "Calendar" tab with Going / Maybe / Can't-go and going counts.
   - *Touches:* `EventController`, `Event`/`EventRsvp` models, admin "Event Calendar" card, resident Calendar panel + JS.
4. **CSV/Excel import** for residents/households (mirror of the existing exports). ✅ **Done (CSV)**
   - *Why:* onboarding a subdivision manually is painful; the dead import UI was removed earlier.
   - *Scope:* staff upload → validate → create residents/households (skips duplicate emails, reports counts/errors).
   - *Touches:* `ResidentController::import`, `POST /api/import/residents`, admin "Import Residents (CSV)" card.

### P2 — Medium (1–2 weeks each)
5. **Document library upgrade** — folders, per-document permissions (resident/officer/board), bulk upload, versioning. Generalize `officer_files`.
6. **Violations / CC&R module** — open a violation against a household, attach photos, deadline, status (Open → Notice → Resolved/Escalated), resident acknowledgement. Reuse the issue workflow patterns + the `issue_audit_logs` style trail.
7. **Online voting & surveys** — motions/options, one vote per resident, majority/percentage results, anonymous option; audit of who voted (not what).
8. **Custom request forms** — gate pass, move-in/out, amenity request; admin-defined fields; routed to officers (reuse the issue pipeline).
9. **Opt-in resident directory** — name, block/lot, contact (if consented), search; hidden by default.
10. **Payment upgrades** — recurring/autopay, partial payments/payment plans, and a second gateway beyond GCash; keep the existing validation queue for manual proofs.

### P3 — Strategic / larger
11. **Amenity/reservation booking** (clubhouse/pool) with availability + approvals.
12. **Visitor/gate access** (QR visitor passes, gate logs) — only if the subdivision needs it (currently out of capstone scope).
13. **AI assistant / insights** (e.g., summarize issues, trend alerts, budget anomalies) — differentiate like FRONTSTEPS ALLi.
14. **Ops maturity** — a status page, in-app feedback/feature-request portal, and a public changelog (HOA Express does this well).

## Notes for the capstone defense
- Improvements 1–4 are **small** and directly strengthen the "communication" and "reports" objectives already claimed in Chapter 1.
- Improvement 2 ties SubSync to the **local RRL** (barangay clearance/blotter systems) and makes the Reports feature demonstrably useful, not just present.
- Everything above is additive; none conflicts with the existing ISO 25010 evaluation criteria (it strengthens functional suitability and usability).

## Sources
- PayHOA features — https://www.payhoa.com/features/
- HOA Express product page — https://www.hoa-express.com/
- FRONTSTEPS product suite — https://www.frontsteps.com/
- Local/contextual systems — `SubSync_Capstone_Full.md` (Chapter 2 RRL)
