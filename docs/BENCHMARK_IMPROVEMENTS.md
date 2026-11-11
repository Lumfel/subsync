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
| **TownSq** | Resident engagement / HOA app | Community feed, requests, violations, accounting services, AI suggested replies, resale docs, mobile apps |
| **RunHOA** | Self-managed HOA suite | Directory, accounting, dues, communications, requests/approvals, violations, documents & minutes, e-voting, budgets, vendor management |
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
| Calendars / events + RSVP | ✅ | ✅ | ✅ | ✅ |
| Voting / surveys / polls | ✗ (recommendations only) | ✅ | ✅ | ◐ |
| Custom / request forms | ✗ (fixed forms) | ✅ | ✅ | ◐ |
| Community directory (opt-in) | ✗ | ◐ | ✅ | ◐ |
| Photo galleries | ✗ | ✗ | ✅ | ◐ |
| Reports / templates / certificates | ✅ staff-only (issue + print) | ✅ | ◐ | ✅ (AI) |
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

## Round 2 review — 2026-10-17 (TownSq, RunHOA + capstone evaluation instrument)

Re-checked against `SubSync_Capstone_Full.md` (hardware/software requirements + the ISO/IEC 25010 evaluation tool, incl. Reliability → *Recoverability* and Security → *Non-repudiation / Accountability* items) and two more systems.

### Highest leverage before the defense — strengthen ISO 25010 ratings
- **Automated backups + tested restore** ✅ **Done (2026-10-17)** — `subsync:backup-database` (mysqldump), daily 02:00 schedule, keeps last 14, admin Settings → System card ("Back up now" + list), restore drill executed (42/42 tables, all counts matched).
- **Audit trail expansion** ✅ **Done (2026-10-17)** — `activity_logs` + observer for announcements, finance, templates, documents, accounts, facilities; admin Settings → System shows the recent activity feed.
- **Email verification + optional 2FA for staff** — *Authenticity*. (Login throttling already exists: 5/min on admin login.)
- **Accessibility pass (WCAG AA)** — *Accessibility* is explicitly rated: screen-reader labels, focus order, touch-target sizes, form error announcements (contrast/keyboard partly done already).
- **Calendar iCal/ICS feed + directory CSV export** — *Interoperability*; cheap wins on top of the existing exports.

### Capstone-document alignment
- Objective 1.4 mentions **"emergency tracking"**: ✅ **Done (2026-10-17)** — one-tap "Report an Emergency" (auto-Critical + staff alerts; officer map already renders open issues red), plus **camera capture** (`capture="environment"`) and existing "Use My GPS"/map-pin tools for the Sensor requirements.
- **Software requirements list MySQL 8.0+** — satisfied (switched to MySQL 8.4.3 on 2026-10-14); cite as a complied requirement.
- Hardware section implies a **mobile app** (Android 8+/iOS 13+): the PWA covers installability — document that. Sensor requirements: "Use My GPS" + map pin already existed; **camera capture** added 2026-10-17 (`capture="environment"` on issue photos).
- Use-case diagram says residents "view available reports", but the panel directed reports to be **admin/officer only** (implemented) — align the manuscript wording or note the revision.
- Data dictionary lists officer `Role_Description`; officer designations were intentionally removed — note the deviation.

### Feature ideas from the new benchmark systems
- **Vendor management** (RunHOA): vendors, contracts, contacts, linked work orders.
- **Meetings & minutes** (RunHOA/TownSq): schedules, agendas, minutes archive, attendance, annual-meeting tools.
- **Late-fee / penalty automation + printable dunning notices** (PayHOA, TownSq print & mail) built on the existing aging buckets.
- **Pet registry** (TownSq tracks pets at scale) — small, community-visible, low effort.
- **Community feed / bulletin board** (TownSq) — moderated resident posts; optional, overlaps with announcements.
- **AI suggested replies for staff** (TownSq AI) — already listed as P3 #13.

> Panel-driven items still open (see `CAPSTONE_DEFENSE_REVISIONS.md`): S2 resident can append details/photos to an issue, S3 map pin accuracy, S5 officer announcement templates + date picker, S6 date-range validation, S8 document library, S11 CSV-not-Excel cleanup, S13 announcement statuses. Benchmark P1 #1 (email/SMS announcements) remains deferred.

## Sources
- PayHOA features — https://www.payhoa.com/features/
- HOA Express product page — https://www.hoa-express.com/
- FRONTSTEPS product suite — https://www.frontsteps.com/
- TownSq — https://www.townsq.io/
- RunHOA — https://www.runhoa.com/
- Local/contextual systems — `SubSync_Capstone_Full.md` (Chapter 2 RRL)
