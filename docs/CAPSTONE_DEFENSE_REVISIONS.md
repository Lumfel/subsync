# SubSync — Capstone Defense: Panel Revisions (transcribed & triaged)

**Source:** Photo of the signed "Minutes of the Capstone Defense (Title, Proposal, and Final)" — BS Information Technology.
**Project:** SUBSYNC — Proponents: Barbas, Guanzon, Salazar, Tibo-oc.
**Approved by:** Jayrelle Usy, PhD (Member) · Giovanni Yen, PhD (Member) · Mark Jarus Talaquines, MIT (Chairman).

> ⚠️ **No code changes were made for this log** (per request). This is a transcription + triage only.
> Handwriting is partly ambiguous — uncertain readings are marked *(?)* and should be confirmed with the panel.

---

## A. Documentation column

| # | Written | Interpretation | Current state | Recommendation |
|---|---|---|---|---|
| D1 | *(circled)* **Realtime tracking** | The documentation must cover the realtime feature | Implemented (SSE + Reverb/WebSocket; fetch-based SSE fallback for ngrok) but **not written up** | Add a "Realtime updates" section to Chapter 3/4 documentation: architecture (SSE bus + observers), fallback, and where it's used |
| D2 | **Exclude [payment]** *(?)* (red underline) | Either "exclude payments from the documentation" **or** "include payment" — ambiguous | Payments exist in the system (GCash submit → admin validation → ledger/receipts), beyond the stated objectives 1.1–1.4 | **Confirm with panel.** If "exclude": drop payment from the write-up/scope diagrams; if "include": add payments as an explicit scope item + ERD/data dictionary entries |

---

## B. System Revision column

| # | Written | Interpretation | Current state | Recommendation |
|---|---|---|---|---|
| S1 | **Change UI** | General UI improvement pass | Largely done: simpler nav + terminology (Issues/Documents/Households), wider sidebar, left-aligned nav, contrast/scrollbar fixes, cleaner fonts | Fold remaining UI polish into the P2 items; capture before/after screenshots for the defense |
| S2 | **Priority level (autoset) based on issue details (auto)** + **resident can add details including pictures** | Auto-priority from the issue text/details, and let the resident add more details (incl. photos) | ✅ **DONE** — auto-priority on create; `POST /api/issues/{id}/details` (owner-only, status ≠ Resolved) appends notes/photos/documents to an open report. Original text untouched (audit trail); additions land in the issue history + media gallery, realtime push to staff. Resident UI: "Add details" on each open report. Tests: `ResidentIssueDetailsTest` (6) | — |
| S3 | **Map Pin / Accurate** | Map pins must be accurate | ✅ **DONE** — picked pin is draggable (fine-tune) with live coordinates, client-side bounds check before submit (server already rejects out-of-bounds), a hint explaining the boundary, and the resident's own issue pins render with a gold ring + "Your report" popup on the map (legend included) | — |
| S4 | **Add database range of subdivision** | Store the subdivision's area/boundary in the DB | `map_settings` holds `min_lat/max_lat/min_lng/max_lng` (defaults in code) | Seed/configure the actual Terra Nova bounds and document the `map_settings` table; expose bounds editing in admin (already editable) |
| S5 | **Pre-templated [Notice] / Announcements, use date picker** *(?)* | Use pre-made templates for notices/announcements, and a date picker for dates | ✅ **DONE** — template quick-fill in the officer create card (Notice/Urgent/Event presets), both portals switch to DB `announcement_templates` buttons when seeded (`GET /api/announcement-templates`); dates use native date pickers | — |
| S6 | **Fixed Date from–to [filter] (improved)** *(?)* | Improve the date range (from–to) filters | ✅ **DONE** — server normalizes date filters (`IssueController::dateRange`): malformed values ignored, reversed ranges swapped, applied consistently to issues list + common-issues; the admin report range swaps client-side too. Tests: `IssueDateFilterTest` | — |
| S7 | **Revise text** | Proofread wording | Nav/terminology already standardised; some copy/capitalisation inconsistencies remain | Run a text/copy audit across all three portals (labels, empty states, errors) |
| S8 | **Check document (subdivision project)** *(?)* | Subdivision project documents / document checking | Officer **files** upload exists (flat); no folders/permissions/versioning | Covered by P2 "document library upgrade"; add a "Subdivision project documents" category |
| S9 | **Validation** | Strengthen input validation | ✅ **DONE** — HOA expense `category`/`status` and policy `status` are now enums matching the UI options; date-range filters normalized (S6); registration requires consent | — |
| S10 | **Change "delinquent"** | Replace the word "delinquent" | ✅ Already done — "delinquent" no longer exists in code; it became "Non-Compliant", and **household status is now Occupied/Vacant** | Nothing to do; cite the change in documentation |
| S11 | **Excel (should be CSV) Payment** | Payment-related files/export should be CSV, not Excel | ✅ **DONE** — payments now have an in-app "Export CSV" button (`/api/export/payments`); the dead SheetJS loader + the whole retired Excel/financial-records JS cluster (~500 lines) were removed; officer card retitled "Send Report File to Admin" with CSV/PDF/Word/image support | — |
| S12 | **Transparency** | Keep/expand financial transparency | ✅ HOA expenses + policies + ledger/aging + payments transparency summary visible to residents | Document it as a feature and add it to the evaluation script; consider a dedicated "Transparency" resident section |
| S13 | **Announcement Status** | Announcement status handling | ✅ **DONE** — **Draft** status added end-to-end: "Save as Draft" in both admin and officer composers, drafts are invisible to residents (server-enforced), staff see a DRAFT badge with a one-click **Publish** (admin list, officer list, view modal) — and the admin archive/restore response-shape bug was fixed along the way. Tests: `AnnouncementDraftTest` | — |

---

## Priority triage (my read)

**High (panel explicitly asked, small effort):**
- ✅ S2 — resident can add details/photos after submitting (implemented 2026-10-23, `ResidentIssueDetailsTest`).
- ✅ S10 — "delinquent" → resolved earlier.
- ✅ S11 — CSV standardization + dead Excel/SheetJS code removed (implemented 2026-10-23).
- ✅ S5 — announcement templates available to officers (implemented 2026-10-23).

**Medium:**
- ✅ S6 — from–to date filters validated/consistent (implemented 2026-10-23).
- ✅ S13 — announcement Draft + publish flow (implemented 2026-10-23).
- ✅ S3 — map pin accuracy: draggable pin, bounds check, own-report highlight (implemented 2026-10-23).

**Documentation-only:**
- D1 — write up realtime tracking.
- D2 — confirm "exclude/include payment".
- S1/S7/S12 — capture UI changes, revise copy, document transparency.

**Needs panel confirmation (ambiguous handwriting):**
- D2 (exclude/include payment), S5 ("We templated…"), S6 wording, S8 ("Check document").

---

## Note
This file records the panel's feedback and a triage only. **No application code was modified.** Say the word and I'll implement the High items first (S2, S11, S5) with tests.
